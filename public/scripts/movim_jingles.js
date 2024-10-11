var MovimJingleSession = function (jid, fullJid, id, name, avatarUrl) {
    this.id = id ?? crypto.randomUUID();
    this.jid = jid;
    this.fullJid = fullJid;
    this.tracksTypes = {};
    this.name = name;
    this.avatarUrl = avatarUrl;

    this.pc = new RTCPeerConnection({ 'iceServers': MovimVisio.services });

    this.participant = document.createElement('div');
    this.participant.dataset.jid = this.jid;
    this.participant.classList.add('participant');
    document.querySelector('#participants').appendChild(this.participant);
    this.participant.classList.add('video_off');
    this.participant.classList.add('audio_off');

    this.remoteVideo = document.createElement('video');
    this.remoteVideo.autoplay = true;
    this.remoteVideo.disablePictureInPicture = true;
    this.remoteVideo.poster = BASE_URI + 'theme/img/empty.png';
    this.participant.appendChild(this.remoteVideo);

    this.remoteAudio = document.createElement('audio');
    this.remoteAudio.autoplay = true;
    this.participant.appendChild(this.remoteAudio);

    if (this.name) {
        this.participant.dataset.name = this.name;
    }

    if (this.avatarUrl) {
        let background = document.createElement('img');
        background.classList.add('avatar');
        background.src = this.avatarUrl;
        this.participant.appendChild(background);
    }

    this.pc.ontrack = event => {
        var srcObject = null;

        if (event.streams && event.streams[0]) {
            srcObject = event.streams[0];
        } else {
            if (!this.inboundStream) {
                this.inboundStream = new MediaStream();
                this.remoteAudio.srcObject = this.inboundStream;
            }

            this.inboundStream.addTrack(event.track);
            srcObject = this.inboundStream;
        }

        if (event.track.kind == 'audio') {
            this.remoteAudio.srcObject = srcObject;
            this.participant.classList.remove('audio_off');
        } else if (event.track.kind == 'video') {
            this.remoteVideo.srcObject = srcObject;
            this.participant.classList.remove('video_off');
        }

        this.tracksTypes['mid' + event.transceiver.mid] = event.track.kind;

        this.handleRemoteAudio();
    }

    this.pc.onicecandidate = event => {
        let candidate = event.candidate;

        if (candidate && candidate.candidate && candidate.candidate.length > 0) {
            Visio_ajaxCandidate(this.fullJid, this.id, event.candidate);
        }
    };

    MovimVisio.localStream.getTracks().forEach(track => {
        this.pc.addTrack(track, MovimVisio.localStream);
    });
}

MovimJingleSession.prototype.handleRemoteAudio = function () {
    this.remoteAudioContext = new AudioContext();

    try {
        var remoteMicrophone = this.remoteAudioContext.createMediaStreamSource(
            this.remoteAudio.srcObject
        );
    } catch (error) {
        MovimUtils.logError(error);
        return;
    }

    var remoteJavascriptNode = this.remoteAudioContext.createScriptProcessor(2048, 1, 1);
    this.isMuteStep = 0;
    this.remoteMaxLevel = 0;

    remoteMicrophone.connect(remoteJavascriptNode);
    remoteJavascriptNode.connect(this.remoteAudioContext.destination);
    remoteJavascriptNode.onaudioprocess = (event) => {
        var inpt = event.inputBuffer.getChannelData(0);
        var instant = 0.0;
        var sum = 0.0;

        for (var i = 0; i < inpt.length; ++i) {
            sum += inpt[i] * inpt[i];
        }

        instant = Math.sqrt(sum / inpt.length);
        this.remoteMaxLevel = Math.max(this.remoteMaxLevel, instant);

        var base = (instant / this.remoteMaxLevel);
        var level = (base > 0.05) ? base ** .3 : 0;

        // Fallback in case we don't have the proper signalisation
        if (level == 0) {
            this.isMuteStep++;
        } else {
            this.isMuteStep = 0;
        }

        if (this.isMuteStep > 250) {
            this.remoteVideo.classList.add('audio_off');
        } else {
            this.remoteVideo.classList.remove('audio_off');
        }

        this.participant.style.setProperty('--level', level.toFixed(2));
    }
}

MovimJingleSession.prototype.terminate = function (reason) {
    Visio_ajaxTerminate(this.fullJid, this.id, reason);
    this.close();
}

MovimJingleSession.prototype.close = function () {
    if (this.pc) {
        this.pc.close();
        this.pc = null;
    }

    if (this.remoteAudioContext) {
        this.remoteAudioContext.close();
        this.remoteAudioContext = null;
    }

    let participant = document.querySelector('.participant[data-jid="' + this.jid + '"]');
    if (participant) participant.remove();

    let remoteVideoStream = this.remoteVideo.srcObject;
    if (remoteVideoStream) {
        remoteVideoStream.getTracks().forEach(track => track.stop());
        remoteVideoStream = null;
    }

    let remoteAudioStream = this.remoteAudio.srcObject;
    if (remoteAudioStream) {
        remoteAudioStream.getTracks().forEach(track => track.stop());
        remoteAudioStream = null;
    }
}

MovimJingleSession.prototype.onCandidate = function (candidate, mid, mlineindex) {
    this.pc.addIceCandidate(new RTCIceCandidate({
        // filter the a=candidate lines
        'candidate': candidate.split(/\n/).filter(line => {
            return line.startsWith('a=candidate');
        }).join('').substring(2),
        'sdpMid': mid,
        'sdpMLineIndex': mlineindex
    })).catch(error => MovimUtils.logError(error));
}

MovimJingleSession.prototype.onContentAdd = function (sdp) {
    this.pc.setRemoteDescription(new RTCSessionDescription({ 'sdp': sdp + "\n", 'type': 'offer' }))
        .catch(error => MovimUtils.logError(error));
}

MovimJingleSession.prototype.sessionInitiate = function (fullJid, id, mujiRoom) {
    this.id = id;
    this.fullJid = fullJid;
    this.pc.createOffer()
        .then(offer => this.pc.setLocalDescription(offer))
        .then(() => Visio_ajaxSessionInitiate(this.fullJid, this.pc.localDescription, id, mujiRoom));
}

MovimJingleSession.prototype.onAcceptSDP = function (sdp) {
    this.pc.setRemoteDescription(new RTCSessionDescription({ 'sdp': sdp + "\n", 'type': 'answer' }))
        .catch(error => {
            this.terminate('incompatible-parameters');
            MovimUtils.logError(error)
        });
}

MovimJingleSession.prototype.onInitiateSDP = function (sdp) {
    this.pc.setRemoteDescription(new RTCSessionDescription({ 'sdp': sdp + "\n", 'type': 'offer' }))
        .then(() => {
            this.pc.createAnswer()
                .then(answer => this.pc.setLocalDescription(answer))
                .then(() => Visio_ajaxSessionAccept(this.fullJid, this.id, this.pc.localDescription))
                .catch(MovimUtils.logError);
        }).catch(error => MovimUtils.logError(error));
}

MovimJingleSession.prototype.onReplaceTrack = function (videoTrack) {
    var sender = this.pc.getSenders().find(s => s.track && s.track.kind == videoTrack.kind);

    if (sender) {
        sender.replaceTrack(videoTrack);
    }
}

MovimJingleSession.prototype.enableTrack = function (enable = true, kind) {
    let rtc = this.pc.getSenders().find(rtc => rtc.track && rtc.track.kind == kind);
    let mid = this.pc.getTransceivers().filter(t => t.sender.track.id == rtc.track.id)[0].mid;

    if (rtc) {
        if (enable) {
            Visio_ajaxMute(this.fullJid, this.id, 'mid' + mid);
        } else {
            Visio_ajaxUnmute(this.fullJid, this.id, 'mid' + mid);
        }
    }
}

MovimJingleSession.prototype.onMute = function (name) {
    if (this.tracksTypes[name]) {
        if (this.tracksTypes[name] == 'audio') {
            this.participant.classList.add('audio_off');
        }

        if (this.tracksTypes[name] == 'video') {
            this.participant.classList.add('video_off');
        }
    }
}

MovimJingleSession.prototype.onUnmute = function (name) {
    if (this.tracksTypes[name]) {
        if (this.tracksTypes[name] == 'audio') {
            this.participant.classList.remove('audio_off');
        }

        if (this.tracksTypes[name] == 'video') {
            this.participant.classList.remove('video_off');
        }
    }
}

MovimJingleSession.prototype.replaceLocalStream = function (stream) {
    let videoTrack = stream.getVideoTracks()[0];
    var sender = this.pc.getSenders().find(s => s.track && videoTrack && s.track.kind == videoTrack.kind);

    if (sender) {
        sender.replaceTrack(videoTrack);
    } else {
        stream.getTracks().forEach(track => {
            this.pc.addTrack(track, stream);
        });
    }
}

var MovimJingles = {
    sessions: {},

    startCalls: function (mujiRoom) {
        for (jid of Object.keys(MovimJingles.sessions)) {
            MovimJingles.onProceed(jid, MovimJingles.sessions[jid].fullJid, MovimJingles.sessions[jid].id, mujiRoom);
        }
    },

    initSession: function (jid, fullJid, id, name, avatarUrl) {
        if (Object.keys(MovimJingles.sessions).includes(jid)) {
            return;
        }

        if (!MovimVisio.localStream) {
            throw Error('localStream is not ready');
        };

        MovimJingles.sessions[jid] = new MovimJingleSession(jid, fullJid, id, name, avatarUrl);
        return MovimJingles.sessions[jid].id;
    },

    enableAudio: function (enable = true) {
        MovimVisio.localStream.getTracks().filter(track => track.kind == 'audio').forEach(track => {
            track.enabled = enable;
        });

        for (jid of Object.keys(MovimJingles.sessions)) {
            MovimJingles.sessions[jid].enableTrack(enable, 'audio');
        }
    },

    enableVideo: function (enable = true) {
        MovimVisio.localStream.getTracks().filter(track => track.kind == 'video').forEach(track => {
            track.enabled = enable;
        });

        for (jid of Object.keys(MovimJingles.sessions)) {
            MovimJingles.sessions[jid].enableTrack(enable, 'video');
        }
    },

    replaceLocalStream: function (stream) {
        for (session of Object.values(MovimJingles.sessions)) {
            session.replaceLocalStream(stream);
        }
    },

    onCandidate: function (jid, candidate, mid, mlineindex) {
        if (MovimJingles.sessions[jid] == undefined) {
            throw Error('Candidate from a non initiated session ' + jid);
        }

        MovimJingles.sessions[jid].onCandidate(candidate, mid, mlineindex);
    },

    onInitiateSDP: function (jid, sdp, sid) {
        if (MovimJingles.sessions[jid] == undefined) {
            throw Error('Initiate SDP from a non initiated session ' + jid);
        }

        // Put the real sid
        MovimJingles.sessions[jid].id = sid;
        MovimJingles.sessions[jid].onInitiateSDP(sdp);
    },

    onAcceptSDP: function (jid, sdp) {
        if (MovimJingles.sessions[jid] == undefined) {
            throw Error('Accept SDP from a non initiated session ' + jid);
        }

        MovimJingles.sessions[jid].onAcceptSDP(sdp);
    },

    onProceed: function (jid, fullJid, id, mujiRoom) {
        if (MovimJingles.sessions[jid] == undefined) {
            throw Error('Proceed from a non initiated session ' + jid);
        }

        MovimJingles.sessions[jid].sessionInitiate(fullJid, id, mujiRoom);
    },

    onMute: function (jid, name) {
        if (MovimJingles.sessions[jid] == undefined) {
            throw Error('Mute from a non initiated session ' + jid);
        }

        MovimJingles.sessions[jid].onMute(name);
    },

    onUnmute: function (jid, name) {
        if (MovimJingles.sessions[jid] == undefined) {
            throw Error('Unmute from a non initiated session ' + jid);
        }

        MovimJingles.sessions[jid].onUnmute(name);
    },

    onContentAdd: function (jid, sdp) {
        if (MovimJingles.sessions[jid] == undefined) {
            throw Error('Content add from a non initiated session ' + jid);
        }

        MovimJingles.sessions[jid].onContentAdd(sdp);
    },

    onReplaceTrack: function (stream) {
        let videoTrack = stream.getVideoTracks()[0];

        for (jid of Object.keys(MovimJingles.sessions)) {
            MovimJingles.sessions[jid].onReplaceTrack(videoTrack);
        }
    },

    terminate: function (jid, reason) {
        MovimJingles.sessions[jid].terminate(reason);
        delete MovimJingles.sessions[jid];
    },

    onTerminate: function (jid) {
        if (MovimJingles.sessions[jid] == undefined) return;

        MovimJingles.sessions[jid].close();
        delete MovimJingles.sessions[jid];

        let visio = document.querySelector('#visio');

        // No sessions left
        if (Object.keys(MovimJingles.sessions).length == 0) {
            if (visio.dataset.muji == 'false') {
                MovimVisio.clear();
            } else {
                let state = document.querySelector('p.state');
                state.innerText = MovimVisio.states.no_participants_left;
            }

        }
    },

    terminateAll: function (reason) {
        for (jid of Object.keys(MovimJingles.sessions)) {
            MovimJingles.terminate(jid, reason);
        }

        MovimVisio.clear();
    }
}