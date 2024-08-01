function logError(error) {
    console.log(error.name + ': ' + error.message);
    console.error(error);
}

var Visio = {
    calling: false,

    videoSelect: undefined,
    switchCamera: undefined,

    inboundStream: null,

    services: [],

    tracksTypes: [],

    prepare: function (from, id, withVideo) {
        Visio_ajaxPrepare(from);

        MovimVisio.from = from;
        MovimVisio.id = id;
        MovimVisio.withVideo = withVideo ?? false;
    },

    init: function () {
        let visio = document.querySelector('#visio');

        visio.dataset.from = MovimVisio.from;

        if (MovimVisio.id) {
            visio.dataset.id = MovimVisio.id;
        }

        delete visio.dataset.type;
        visio.dataset.type = (MovimVisio.withVideo) ? 'video' : 'audio';

        if (MovimVisio.withVideo) {
            MovimVisio.localVideo = document.getElementById('local_video');
            MovimVisio.remoteVideo = document.getElementById('remote_video');
            MovimVisio.remoteVideo.disablePictureInPicture = true;
            MovimVisio.screenSharing = document.getElementById('screen_sharing_video');
        }

        MovimVisio.localAudio = document.getElementById('local_audio');
        MovimVisio.remoteAudio = document.getElementById('remote_audio');

        var configuration = {
            'iceServers': Visio.services
        };

        MovimVisio.pc = new RTCPeerConnection(configuration);

        MovimVisio.pc.ontrack = event => {console.log(event.track);
            if (MovimVisio.withVideo) {
                if (event.streams && event.streams[0]) {
                    MovimVisio.remoteVideo.srcObject = event.streams[0];
                } else {
                    if (!MovimVisio.inboundStream) {
                        MovimVisio.inboundStream = new MediaStream();
                        MovimVisio.remoteVideo.srcObject = MovimVisio.inboundStream;
                    }
                    MovimVisio.inboundStream.addTrack(event.track);
                }

                VisioUtils.setRemoteAudioState('mic');
                VisioUtils.setRemoteVideoState('videocam');
            } else {
                if (event.streams && event.streams[0]) {
                    MovimVisio.remoteAudio.srcObject = event.streams[0];
                } else {
                    if (!MovimVisio.inboundStream) {
                        MovimVisio.inboundStream = new MediaStream();
                        MovimVisio.remoteAudio.srcObject = MovimVisio.inboundStream;
                    }
                    MovimVisio.inboundStream.addTrack(event.track);
                }
                VisioUtils.handleRemoteAudio();
                VisioUtils.setRemoteAudioState('mic');
            }



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
        Visio.services = services;
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
            var tracks = stream.getTracks();
            for (var i = 0; i < tracks.length; i++) {
                if (tracks[i].getSettings().channelCount) {
                    localStorage.setItem('defaultMicrophone', tracks[i].getSettings().deviceId);
                } else {
                    localStorage.setItem('defaultCamera', tracks[i].getSettings().deviceId);
                }
            }

            if (!MovimVisio.withVideo) {
                MovimVisio.localAudio.srcObject = stream;
            } else {
                Visio.switchCamera.classList.remove('disabled');
                MovimVisio.localVideo.srcObject = stream;

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
            if (MovimVisio.pc.getSenders().length == 0) {
                stream.getTracks().forEach(track => MovimVisio.pc.addTrack(track, stream));

                if (MovimVisio.id) {
                    Visio_ajaxAccept(MovimVisio.from, MovimVisio.id);
                } else {
                    // TODO launch when button pressed
                    MovimVisio.id = Math.random().toString(36).substring(2, 11);
                    Visio.calling = true;
                    VisioUtils.toggleMainButton();
                    Visio_ajaxPropose(MovimVisio.from, MovimVisio.id, MovimVisio.withVideo);
                }
            }
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
        console.log(sdp);
        MovimVisio.pc.setRemoteDescription(new RTCSessionDescription({ 'sdp': sdp + "\n", 'type': 'offer' }), () => {
            MovimVisio.pc.createAnswer().then(function (answer) {
                return MovimVisio.pc.setLocalDescription(answer);
            }).then(function () {
                Visio_ajaxSessionAccept(MovimVisio.pc.localDescription, MovimVisio.from, MovimVisio.id);
            }).catch(logError);
        }, logError);
    },

    onContentAdd: function (sdp) {
        console.log('ADD');
        console.log(sdp);
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
        if (!MovimVisio.withVideo) {
            let localStream = MovimVisio.localAudio.srcObject;

            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }

            let remoteStream = MovimVisio.remoteAudio.srcObject;

            if (remoteStream) {
                remoteStream.getTracks().forEach(track => track.stop());
            }

            MovimVisio.localAudio.srcObject = null;
            MovimVisio.remoteAudio.srcObject = null;
        } else {
            let localStream = MovimVisio.localVideo.srcObject;

            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }

            let remoteStream = MovimVisio.remoteVideo.srcObject;

            if (remoteStream) {
                remoteStream.getTracks().forEach(track => track.stop());
            }

            MovimVisio.localVideo.srcObject = null;
            MovimVisio.remoteVideo.srcObject = null;

            MovimVisio.localVideo.classList.add('hide');
        }


        if (MovimVisio.pc) MovimVisio.pc.close();

        document.querySelector('p.state').innerText = reason == 'decline'
            ? Visio.states.declined
            : Visio.states.ended;
        button = document.querySelector('#main');

        button.className = 'button action color red';
        button.querySelector('i').className = 'material-symbols';
        button.querySelector('i').innerText = 'close';

        button.onclick = () => {
            //window.close();
            Visio.goodbye();
        }

        // And we force close the window after 2sec
        window.setTimeout(() => {
            //window.close();
            Visio.goodbye();
        }, 2000);
    },

    goodbye: (reason) => {
        Visio.onTerminate(reason);

        let visio = document.querySelector('#visio');
        delete visio.dataset.from;
        delete visio.dataset.id;
        delete visio.dataset.type;

        if (MovimVisio.id) {
            Visio_ajaxEnd(MovimVisio.from, MovimVisio.id, reason);
        }

        MovimVisio.clear();
    },
}

MovimWebsocket.attach(() => {
    Visio_ajaxResolveServices();
    Visio_ajaxGetStates();
});
