var MovimJingleSession = function (jid, fullJid, id, name, avatarUrl) {
    this.id = id ?? crypto.randomUUID();
    this.jid = jid;
    this.fullJid = fullJid;
    this.tracksTypes = {};
    this.name = name;
    this.avatarUrl = avatarUrl;
    this.audioLevel = 0;
    this.lastPostMessage = 0;

    this.pc = new RTCPeerConnection({ 'iceServers': MovimVisio.services });

    this.participant = document.createElement('div');
    this.participant.dataset.jid = this.jid;
    this.participant.classList.add('participant');

    document.querySelector('#participants').appendChild(this.participant);

    this.participant.classList.add('video_off');
    this.participant.classList.add('screen_off');
    this.participant.classList.add('audio_off');

    this.remoteScreenVideo = document.createElement('video');
    this.remoteScreenVideo.classList.add('screen');
    this.remoteScreenVideo.autoplay = true;
    this.remoteScreenVideo.disablePictureInPicture = true;
    this.remoteScreenVideo.poster = BASE_URI + 'theme/img/empty.png';
    this.participant.appendChild(this.remoteScreenVideo);

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
            // Fallback code
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

            this.tracksTypes['mid' + event.transceiver.mid] = 'audio';

            this.handleRemoteAudio();
        } else if (event.track.kind == 'video') {
            if (this.remoteVideo.srcObject && this.remoteVideo.srcObject.id != srcObject.id) {
                this.remoteScreenVideo.srcObject = srcObject;
                this.tracksTypes['mid' + event.transceiver.mid] = 'screen';
                this.remoteScreenVideo.oncanplay = event => {
                    this.participant.classList.remove('screen_off');
                }
            } else {
                this.remoteVideo.srcObject = srcObject;
                this.tracksTypes['mid' + event.transceiver.mid] = 'video';
                this.participant.classList.remove('video_off');
            }
        }
    }

    this.pc.onnegotiationneeded = event => {
        if (this.pc.localDescription) {
            this.oldLocalDescription = this.pc.localDescription.sdp;

            this.pc.createOffer()
                .then((offer) => this.pc.setLocalDescription(offer))
                .then(() => this.updateContent())
                .catch(err => MovimUtils.logError(err));
        }
    };

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

MovimJingleSession.prototype.handleRemoteAudio = async function () {
    this.remoteAudioContext = new AudioContext();

    try {
        var remoteMicrophone = this.remoteAudioContext.createMediaStreamSource(
            this.remoteAudio.srcObject
        );

        await this.remoteAudioContext.audioWorklet.addModule(BASE_URI + 'scripts/movim_jingle_session_audio_worklet.js');
        const audioWorkletNode = new AudioWorkletNode(this.remoteAudioContext, 'jinglesession-audioworklet');
        remoteMicrophone.connect(audioWorkletNode);

        audioWorkletNode.port.onmessage = (e) => {
            this.processRemoteAudioMessage(e.data);
        };

    } catch (error) {
        MovimUtils.logError(error);
        return;
    }
}

/**
 * In some clients, such as Dino, the buffer is stopped when the microphone is muted
 * we detect that there and send a clear message properly
 */
MovimJingleSession.prototype.clearRemoteAudioMessage = function () {
    const second = 1000;
    const secondAgo = Date.now() - second;

    if (this.lastPostMessage < secondAgo) {
        this.processRemoteAudioMessage({
            "isMuteStep": 5,
            "level": 0,
            "published": Date.now()
        });
    }
}

MovimJingleSession.prototype.processRemoteAudioMessage = function (message) {
    if (message.isMuteStep >= 5) {
        this.remoteVideo.classList.add('audio_off');
    } else {
        this.remoteVideo.classList.remove('audio_off');
    }

    this.lastPostMessage = message.published;
    this.audioLevel = message.level;
    this.participant.style.setProperty('--level', message.level);

    if (message.level > 0) {
        clearTimeout(this.clearRemoteAudioMessageId);
        this.clearRemoteAudioMessageId = setTimeout(this.clearRemoteAudioMessage.bind(this), 2000);
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

MovimJingleSession.prototype.onContentAdd = function (sdp, mid) {
    remoteDescription = this.pc.remoteDescription.sdp;
    remoteDescription += sdp.match('m=[^]*');
    remoteDescription = remoteDescription.replace(/^a=group.*/m, sdp.match(/a=group.*/)[0]);

    this.pc.setRemoteDescription({ 'sdp': remoteDescription + "\n", 'type': 'offer' })
        .then(() => {
            this.pc.createAnswer()
                .then(answer => this.pc.setLocalDescription(answer))
                .then(() => Visio_ajaxSessionAccept(this.fullJid, this.id, this.pc.localDescription))
                .catch(MovimUtils.logError);
        })
        .catch(error => MovimUtils.logError(error));
}

MovimJingleSession.prototype.onContentModify = function (sdp, mid) {
    // TODO, implement mid

    this.pc.setRemoteDescription({ 'sdp': sdp + "\n", 'type': 'offer' })
        .then(() => {
            this.pc.createAnswer()
                .then(answer => this.pc.setLocalDescription(answer))
                .then(() => Visio_ajaxSessionAccept(this.fullJid, this.id, this.pc.localDescription))
                .catch(MovimUtils.logError);
        })
        .catch(error => MovimUtils.logError(error));
}

MovimJingleSession.prototype.onContentRemove = function (sdp, mid) {
    remoteDescription = this.pc.remoteDescription.sdp;
    let parts = remoteDescription.split('m=');

    let filtered = parts.filter(part => !part.includes('a=mid:' + mid));

    remoteDescription = filtered.join('m=');
    this.pc.setRemoteDescription({ 'sdp': sdp + "\n", 'type': 'offer' })
        .then(() => {
            this.pc.createAnswer()
                .then(answer => this.pc.setLocalDescription(answer))
                .then(() => Visio_ajaxSessionAccept(this.fullJid, this.id, this.pc.localDescription))
                .catch(MovimUtils.logError);
        })
        .catch(error => MovimUtils.logError(error));
}

MovimJingleSession.prototype.sessionInitiate = function (fullJid, id, mujiRoom) {
    this.id = id;
    this.fullJid = fullJid;
    this.pc.createOffer()
        .then(offer => this.pc.setLocalDescription(offer))
        .then(() => Visio_ajaxSessionInitiate(this.fullJid, this.pc.localDescription, id, mujiRoom));
}

MovimJingleSession.prototype.updateContent = function () {
    let oldMedias = this.oldLocalDescription.match(MovimVisio.bundleRegex)[2].split(' ');
    let newMedias = this.pc.localDescription.sdp.match(MovimVisio.bundleRegex)[2].split(' ');

    let createdMedias = newMedias.filter((e) => !oldMedias.includes(e));
    let destroyedMedias = oldMedias.filter((e) => !newMedias.includes(e));

    let changed = false;

    createdMedias.forEach(mid => {
        changed = true;
        Visio_ajaxContentAdd(this.fullJid, this.pc.localDescription.sdp, this.id, mid);
    });

    destroyedMedias.forEach(mid => {
        changed = true;
        Visio_ajaxContentRemove(this.fullJid, this.oldLocalDescription, this.id, mid);
    });

    // Nothing added, nothing removed, lets update everything...
    if (changed == false) {
        Visio_ajaxContentModify(this.fullJid, this.pc.localDescription.sdp, this.id);
    }
}

MovimJingleSession.prototype.onAcceptSDP = function (sdp) {
    this.pc.setRemoteDescription({ 'sdp': sdp + "\n", 'type': 'answer' })
        .catch(error => {
            MovimVisio.goodbye('incompatible-parameters');
            MovimUtils.logError(error);
        });
}

MovimJingleSession.prototype.onInitiateSDP = function (sdp) {
    this.pc.setRemoteDescription({ 'sdp': sdp + "\n", 'type': 'offer' })
        .then(() => {
            this.pc.createAnswer()
                .then(answer => this.pc.setLocalDescription(answer))
                .then(() => Visio_ajaxSessionAccept(this.fullJid, this.id, this.pc.localDescription))
                .catch(MovimUtils.logError);
        }).catch(error => MovimUtils.logError(error));
}

MovimJingleSession.prototype.enableScreenSharing = function () {
    track = MovimVisio.screenSharing.srcObject.getTracks()[0];

    if (this.screenSharingSender) {
        this.screenSharingSender.replaceTrack(track).then(() => {
            this.unmute(track);
        });
    } else {
        this.screenSharingSender = this.pc.addTrack(track);
    }
}

MovimJingleSession.prototype.disableScreenSharing = function () {
    if (this.pc && MovimVisio.screenSharing.srcObject) {
        this.mute(MovimVisio.screenSharing.srcObject.getTracks()[0]);
    }
}

MovimJingleSession.prototype.resolveMid = function (track) {
    transceiver = this.pc.getTransceivers().find(transceiver => transceiver.sender.track.id == track.id);
    return transceiver ? transceiver.mid : null;
}

MovimJingleSession.prototype.mute = function (track) {
    if (mid = this.resolveMid(track)) {
        Visio_ajaxMute(this.fullJid, this.id, 'mid' + mid);
    }
}

MovimJingleSession.prototype.unmute = function (track) {
    if (mid = this.resolveMid(track)) {
        Visio_ajaxUnmute(this.fullJid, this.id, 'mid' + mid);
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

        if (this.tracksTypes[name] == 'screen') {
            this.participant.classList.add('screen_off');
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

        if (this.tracksTypes[name] == 'screen') {
            this.participant.classList.remove('screen_off');
        }
    }
}

MovimJingleSession.prototype.insertDtmf = function (s) {
    var rtc = this.pc.getSenders().find(rtc => rtc.track && rtc.track.kind == 'audio');

    if (!rtc || !rtc.dtmf.canInsertDTMF) return;

    rtc.dtmf.insertDTMF(s);
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

    checkActiveSpeaker: function () {
        let maxLevel = 0;
        let maxJid = null;

        for (jid of Object.keys(MovimJingles.sessions)) {
            if (maxLevel < MovimJingles.sessions[jid].audioLevel) {
                maxLevel = MovimJingles.sessions[jid].audioLevel;
                maxJid = jid;
            }
        }

        if (maxJid != null) {
            for (jid of Object.keys(MovimJingles.sessions)) {
                MovimJingles.sessions[jid].participant.classList.remove('active');
            }

            MovimJingles.sessions[maxJid].participant.classList.add('active');
        }
    },

    onManageTrack: function (stream, enable) {
        for (jid of Object.keys(MovimJingles.sessions)) {
            MovimJingles.sessions[jid].onManageTrack(stream, enable);
        }
    },

    enableScreenSharing: function () {
        for (jid of Object.keys(MovimJingles.sessions)) {
            MovimJingles.sessions[jid].enableScreenSharing();
        }
    },

    disableScreenSharing: function () {
        for (jid of Object.keys(MovimJingles.sessions)) {
            MovimJingles.sessions[jid].disableScreenSharing();
        }
    },

    enableAudio: function (enable = true) {
        MovimVisio.localStream.getTracks().filter(track => track.kind == 'audio').forEach(track => {
            track.enabled = enable;

            for (jid of Object.keys(MovimJingles.sessions)) {
                if (enable) {
                    MovimJingles.sessions[jid].unmute(track);
                } else {
                    MovimJingles.sessions[jid].mute(track);
                }
            }
        });
    },

    enableVideo: function (enable = true) {
        MovimVisio.localStream.getTracks().filter(track => track.kind == 'video').forEach(track => {
            track.enabled = enable;

            for (jid of Object.keys(MovimJingles.sessions)) {
                if (enable) {
                    MovimJingles.sessions[jid].unmute(track);
                } else {
                    MovimJingles.sessions[jid].mute(track);
                }
            }
        });
    },

    insertDtmf: function (s) {
        for (jid of Object.keys(MovimJingles.sessions)) {
            MovimJingles.sessions[jid].insertDtmf(s);
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

    onContentAdd: function (jid, sdp, mid) {
        if (MovimJingles.sessions[jid] == undefined) {
            throw Error('Content add from a non initiated session ' + jid);
        }

        MovimJingles.sessions[jid].onContentAdd(sdp, mid);
    },

    onContentModify: function (jid, sdp, mid) {
        if (MovimJingles.sessions[jid] == undefined) {
            throw Error('Content modify from a non initiated session ' + jid);
        }

        MovimJingles.sessions[jid].onContentModify(sdp, mid);
    },

    onContentRemove: function (jid, sdp, mid) {
        if (MovimJingles.sessions[jid] == undefined) {
            throw Error('Content remove from a non initiated session ' + jid);
        }

        MovimJingles.sessions[jid].onContentRemove(sdp, mid);
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