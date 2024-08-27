function logError(error) {
    console.log(error.name + ': ' + error.message);
    console.error(error);
}

var Visio = {
    calling: false,

    videoSelect: undefined,
    switchCamera: undefined,

    inboundStream: null,

    tracksTypes: [],

    prepare: function (from, id, withVideo) {
        Visio_ajaxPrepare(from);

        MovimVisio.from = from;
        MovimVisio.id = id;
        MovimVisio.withVideo = withVideo ?? false;
    },

    init: function (bareFrom) {
        let visio = document.querySelector('#visio');

        visio.dataset.from = bareFrom;

        delete visio.dataset.type;
        visio.dataset.type = (MovimVisio.withVideo) ? 'video' : 'audio';

        MovimVisio.localVideo = document.getElementById('local_video');
        MovimVisio.remoteVideo = document.getElementById('remote_video');
        MovimVisio.remoteVideo.disablePictureInPicture = true;
        MovimVisio.screenSharing = document.getElementById('screen_sharing_video');

        MovimVisio.localAudio = document.getElementById('local_audio');
        MovimVisio.remoteAudio = document.getElementById('remote_audio');

        MovimVisio.pc = new RTCPeerConnection({ 'iceServers': MovimVisio.services });

        MovimVisio.pc.ontrack = event => {
            var srcObject = null;

            if (event.streams && event.streams[0]) {
                srcObject = event.streams[0];
            } else {
                if (!MovimVisio.inboundStream) {
                    MovimVisio.inboundStream = new MediaStream();
                    MovimVisio.remoteAudio.srcObject = MovimVisio.inboundStream;
                }

                MovimVisio.inboundStream.addTrack(event.track);
                srcObject = MovimVisio.inboundStream;
            }

            VisioUtils.setRemoteVideoState('');

            if (event.track.kind == 'audio') {
                MovimVisio.remoteAudio.srcObject = srcObject;
                VisioUtils.setRemoteAudioState('mic');
            } else if (event.track.kind == 'video') {
                MovimVisio.remoteVideo.srcObject = srcObject;
                VisioUtils.setRemoteVideoState('videocam');
            }

            VisioUtils.handleRemoteAudio();
            Visio.tracksTypes['mid' + event.transceiver.mid] = event.track.kind;
        };

        MovimVisio.pc.onicecandidate = event => {
            let candidate = event.candidate;
            if (candidate && candidate.candidate && candidate.candidate.length > 0) {
                Visio_ajaxCandidate(event.candidate, MovimVisio.from, MovimVisio.id);
            }
        };

        MovimVisio.pc.oniceconnectionstatechange = () => VisioUtils.toggleMainButton();

        MovimVisio.pc.onicegatheringstatechange = function (event) {
            // When we didn't receive the WebRTC termination before Jingle
            if (MovimVisio.pc.iceConnectionState == 'disconnected') {
                Visio.onTerminate();
            }

            VisioUtils.toggleMainButton();
        };

        VisioUtils.toggleMainButton();

        if (MovimVisio.withVideo) {
            VisioUtils.switchCameraSetup();
        }

        Visio.getStream();
    },

    onMute: function (name) {
        if (Visio.tracksTypes[name]) {
            if (Visio.tracksTypes[name] == 'audio') {
                VisioUtils.setRemoteAudioState('mic_off');
            }

            if (Visio.tracksTypes[name] == 'video') {
                document.querySelector('#remote_video').classList.add('muted');
                VisioUtils.setRemoteVideoState('videocam_off');
            }
        }
    },

    onUnmute: function (name) {
        if (Visio.tracksTypes[name]) {
            if (Visio.tracksTypes[name] == 'audio') {
                VisioUtils.setRemoteAudioState('mic');
            }

            if (Visio.tracksTypes[name] == 'video') {
                document.querySelector('#remote_video').classList.remove('muted');
                VisioUtils.setRemoteVideoState('videocam');
            }
        }
    },

    setServices: function (services) {
        MovimVisio.services = services;
    },

    setStates: function (states) {
        Visio.states = states;
    },

    getStream: function () {
        if (MovimVisio.withVideo) {
            // On Android where you can't have both camera enabled at the same time
            var videoTrack = MovimVisio.pc.getSenders().find(rtc => rtc.track && rtc.track.kind == 'video');
            if (videoTrack) videoTrack.track.stop();

            Visio.switchCamera.classList.add('disabled');
        }

        var constraints = {
            audio: true,
            video: false
        };

        if (localStorage.getItem('defaultMicrophone')) {
            constraints.audio = {
                deviceId: localStorage.getItem('defaultMicrophone')
            }
        }

        if (MovimVisio.withVideo) {
            const videoSource = Visio.videoSelect.value;
            var defaultCamera = undefined;

            if (localStorage.getItem('defaultCamera')) {
                defaultCamera = localStorage.getItem('defaultMicrophone');
            }

            constraints.video = {
                deviceId: videoSource ? { exact: videoSource } : defaultCamera,
                facingMode: 'user',
                width: { ideal: 4096 },
                height: { ideal: 4096 }
            };
        }

        navigator.mediaDevices.getUserMedia(constraints).then(stream => {
            stream.getTracks().forEach(track => {
                MovimVisio.pc.addTrack(track, stream);

                if (track.kind == 'audio') {
                    MovimVisio.localAudio.srcObject = stream;
                    localStorage.setItem('defaultMicrophone', track.getSettings().deviceId);
                } else if (track.kind == 'video') {
                    MovimVisio.localVideo.srcObject = stream;
                    localStorage.setItem('defaultCamera', track.getSettings().deviceId);
                }
            });

            if (MovimVisio.withVideo) {
                Visio.switchCamera.classList.remove('disabled');

                // Toggle video icon
                var cameraIcon = document.querySelector('#toggle_video i');
                cameraIcon.innerText = 'videocam';

                // Switch camera
                VisioUtils.pcReplaceTrack(stream);
                VisioUtils.enableScreenSharingButton();
            }

            VisioUtils.handleAudio();
            VisioUtils.toggleMainButton();

            // For the first time we attach all the tracks and we launch the call
            if (MovimVisio.id) {
                Visio_ajaxAccept(MovimVisio.from, MovimVisio.id);
            } else {
                // TODO launch when button pressed
                MovimVisio.id = crypto.randomUUID();
                Visio.calling = true;
                VisioUtils.toggleMainButton();
                Visio_ajaxPropose(MovimVisio.from, MovimVisio.id, MovimVisio.withVideo);
            }
            //}
        }, logError);
    },

    gotQuickStream: function () {
        VisioUtils.pcReplaceTrack(MovimVisio.localVideo.srcObject);
    },

    gotScreen: function () {
        VisioUtils.pcReplaceTrack(MovimVisio.screenSharing.srcObject);
    },

    onCandidate: function (candidate, mid, mlineindex) {
        // filter the a=candidate lines
        var filtered = candidate.split(/\n/).filter(line => {
            return line.startsWith('a=candidate');
        });

        MovimVisio.pc.addIceCandidate(new RTCIceCandidate({
            'candidate': filtered.join('').substring(2),
            'sdpMid': mid,
            'sdpMLineIndex': mlineindex
        }), () => { }, logError);
    },

    onProceed: function (from, id) {
        if (from.substring(0, MovimVisio.from.length) == MovimVisio.from && MovimVisio.id == id) {
            // We set the remote resource
            MovimVisio.from = from;

            MovimVisio.pc.createOffer().then(function (offer) {
                Visio.calling = false;
                VisioUtils.toggleMainButton();
                return MovimVisio.pc.setLocalDescription(offer);
            })
                .then(function () {
                    Visio_ajaxSessionInitiate(MovimVisio.pc.localDescription, MovimVisio.from, MovimVisio.id);
                });
        } else {
            console.error('Wrong call')
        }
    },

    onInitiateSDP: function (sdp) {
        MovimVisio.pc.setRemoteDescription(new RTCSessionDescription({ 'sdp': sdp + "\n", 'type': 'offer' }), () => {
            MovimVisio.pc.createAnswer().then(function (answer) {
                return MovimVisio.pc.setLocalDescription(answer);
            }).then(function () {
                Visio_ajaxSessionAccept(MovimVisio.pc.localDescription, MovimVisio.from, MovimVisio.id);
            }).catch(logError);
        }, logError);
    },

    onContentAdd: function (sdp) {
        MovimVisio.pc.setRemoteDescription(new RTCSessionDescription({ 'sdp': sdp + "\n", 'type': 'offer' }), () => {
        }, logError);
    },

    onAcceptSDP: function (sdp) {
        MovimVisio.pc.setRemoteDescription(
            new RTCSessionDescription({ 'sdp': sdp + "\n", 'type': 'answer' }), () => { },
            (error) => {
                Visio.goodbye('incompatible-parameters');
                logError(error)
            }
        );
    },

    onTerminate: (reason) => {
        if (MovimVisio.localAudio) {
            let localStream = MovimVisio.localAudio.srcObject;

            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }

            MovimVisio.localAudio.srcObject = null;
        }

        if (MovimVisio.remoteAudio) {
            let remoteStream = MovimVisio.remoteAudio.srcObject;

            if (remoteStream) {
                remoteStream.getTracks().forEach(track => track.stop());
            }

            MovimVisio.remoteAudio.srcObject = null;
        }

        if (MovimVisio.localVideo) {
            let localStream = MovimVisio.localVideo.srcObject;

            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }

            MovimVisio.localVideo.srcObject = null;
        }

        if (MovimVisio.remoteVideo) {
            let remoteStream = MovimVisio.remoteVideo.srcObject;

            if (remoteStream) {
                remoteStream.getTracks().forEach(track => track.stop());
            }

            MovimVisio.remoteVideo.srcObject = null;
        }

        if (MovimVisio.pc) MovimVisio.pc.close();

        if (VisioUtils.audioContext) {
            VisioUtils.audioContext.close();
            VisioUtils.audioContext = null;
        }

        if (VisioUtils.remoteAudioContext) {
            VisioUtils.remoteAudioContext.close();
            VisioUtils.remoteAudioContext = null;
        }

        document.querySelector('p.state').innerText = reason == 'decline'
            ? Visio.states.declined
            : Visio.states.ended;
        button = document.querySelector('#main');

        button.className = 'button action color red';
        button.querySelector('i').className = 'material-symbols';
        button.querySelector('i').innerText = 'close';

        button.onclick = () => {
            Visio.goodbye();
        }
    },

    goodbye: (reason) => {
        Visio.onTerminate(reason);

        let visio = document.querySelector('#visio');
        delete visio.dataset.from;
        delete visio.dataset.type;

        if (document.fullscreenElement) {
            document.exitFullscreen();
        }

        if (MovimVisio.id) {
            Visio_ajaxEnd(MovimVisio.from, MovimVisio.id, reason);
        }

        MovimVisio.clear();
    },
}

MovimWebsocket.attach(() => {
    if (MovimVisio.services.length == 0) {
        Visio_ajaxResolveServices();
    }

    Visio_ajaxGetStates();
});
