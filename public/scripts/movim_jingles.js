var MovimJingleSession = function (jid, fullJid, id) {
    this.id = id ?? crypto.randomUUID();
    this.jid = jid;
    this.fullJid = fullJid;
    this.tracksTypes = {};

    this.pc = new RTCPeerConnection({ 'iceServers': MovimVisio.services });

    this.remoteVideo = document.createElement('video');
    this.remoteVideo.autoplay = true;
    this.remoteVideo.disablePictureInPicture = true;
    this.remoteVideo.classList.add('remote_video');
    this.remoteVideo.poster = BASE_URI + 'theme/img/empty.png';
    this.remoteVideo.dataset.jid = this.jid;

    document.querySelector('#remote_videos').appendChild(this.remoteVideo);

    this.remoteAudio = document.createElement('audio');
    this.remoteAudio.autoplay = true;
    this.remoteAudio.classList.add('remote_audio');
    this.remoteAudio.dataset.jid = this.jid;

    document.querySelector('#remote_audios').appendChild(this.remoteAudio);

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
        } else if (event.track.kind == 'video') {
            this.remoteVideo.srcObject = srcObject;
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
        logError(error);
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
        var level = (base > 0.01) ? base ** .3 : 0;

        // Fallback in case we don't have the proper signalisation
        if (level == 0) {
            this.isMuteStep++;
        } else {
            this.isMuteStep = 0;
        }

        if (this.isMuteStep > 250) {
            this.remoteVideo.classList.add('mic_off');
        } else {
            this.remoteVideo.classList.remove('mic_off');
        }

        this.remoteVideo.style.setProperty('--level', level);
    }
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

    let video = document.querySelector('#remote_videos video[data-jid="' + this.jid + '"]');
    if (video) video.remove();

    let remoteVideoStream = this.remoteVideo.srcObject;
    if (remoteVideoStream) {
        remoteVideoStream.getTracks().forEach(track => track.stop());
        remoteVideoStream = null;
    }

    let audio = document.querySelector('#remote_audios audio[data-jid="' + this.jid + '"]');
    if (audio) audio.remove();

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
    })).catch(error => logError(error));
}

MovimJingleSession.prototype.onContentAdd = function (sdp) {
    this.pc.setRemoteDescription(new RTCSessionDescription({ 'sdp': sdp + "\n", 'type': 'offer' }))
        .catch(error => logError(error));
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
            Visio.goodbye('incompatible-parameters');
            logError(error)
        });
}

MovimJingleSession.prototype.onInitiateSDP = function (sdp) {
    this.pc.setRemoteDescription(new RTCSessionDescription({ 'sdp': sdp + "\n", 'type': 'offer' }))
        .then(() => {
            this.pc.createAnswer()
                .then(answer => this.pc.setLocalDescription(answer))
                .then(() => Visio_ajaxSessionAccept(this.fullJid, this.id, this.pc.localDescription))
                .catch(logError);
        }).catch(error => logError(error));
}

MovimJingleSession.prototype.onMute = function (name) {
    if (this.tracksTypes[name]) {
        /*if (Visio.tracksTypes[name] == 'audio') {
            VisioUtils.setRemoteAudioState('mic_off');
        }

        if (Visio.tracksTypes[name] == 'video') {
            document.querySelector('#remote_video').classList.add('muted');
            VisioUtils.setRemoteVideoState('videocam_off');
        }*/
    }
},

MovimJingleSession.prototype.onUnmute = function (name) {
    if (this.tracksTypes[name]) {
        /*if (Visio.tracksTypes[name] == 'audio') {
            VisioUtils.setRemoteAudioState('mic');
        }

        if (Visio.tracksTypes[name] == 'video') {
            document.querySelector('#remote_video').classList.remove('muted');
            VisioUtils.setRemoteVideoState('videocam');
        }*/
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

    initSession: function (jid, fullJid, id) {
        if (!MovimVisio.localStream) {
            throw Error('localStream is not ready');
        };

        MovimJingles.sessions[jid] = new MovimJingleSession(jid, fullJid, id);
        return MovimJingles.sessions[jid].id;
    },

    replaceLocalStream: function (stream) {
        for (session of Object.values(MovimJingles.sessions)) {
            session.replaceLocalStream(stream);
        }
    },

    onCandidate: function (jid, candidate, mid, mlineindex) {
        if (MovimJingles.sessions[jid] == undefined) {
            throw Error('Candidate from a non initiated session' + jid);
        }

        MovimJingles.sessions[jid].onCandidate(candidate, mid, mlineindex);
    },

    onInitiateSDP: function (jid, sdp) {
        if (MovimJingles.sessions[jid] == undefined) {
            throw Error('Initiate SDP from a non initiated session ' + jid);
        }

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

    closeSessions: function () {
        for (jid of Object.keys(MovimJingles.sessions)) {
            MovimJingles.closeSession(jid);
        }
    },

    closeSession: function (jid) {
        MovimJingles.sessions[jid].close();
        delete MovimJingles.sessions[jid];
    }
}