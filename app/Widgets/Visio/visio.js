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
        if (!MovimVisio.localStream) return;

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

        MovimVisio.load();

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

        if (MovimVisio.withVideo) {
            VisioUtils.pcReplaceTrack(MovimVisio.localStream);
        }

        VisioUtils.toggleMainButton();

        MovimVisio.localStream.getTracks().forEach(track => {
            MovimVisio.pc.addTrack(track, MovimVisio.localStream);
        });

        if (MovimVisio.withVideo) {
            VisioUtils.switchCameraInCall();
        }

        if (MovimVisio.id) {
            Visio_ajaxAccept(MovimVisio.from, MovimVisio.id);
        } else {
            MovimVisio.id = crypto.randomUUID();
            Visio.calling = true;
            VisioUtils.toggleMainButton();
            Visio_ajaxPropose(MovimVisio.from, MovimVisio.id, MovimVisio.withVideo);
        }
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

    lobbySetup: function (withVideo) {
        Visio.getUserMedia(withVideo);
    },

    getUserMedia: function(withVideo) {
        var constraints = {
            audio: true,
            video: false,
        };

        if (withVideo) {
            constraints.video = {
                facingMode: 'user',
                width: { ideal: 4096 },
                height: { ideal: 4096 }
            }

            if (localStorage.defaultCamera) {
                constraints.video = {
                    deviceId: localStorage.defaultCamera
                };
            }
        }

        if (localStorage.defaultMicrophone) {
            constraints.audio = {
                deviceId: localStorage.defaultMicrophone
            }
        }

        MovimVisio.load();

        let lobby = document.querySelector('#visio_lobby');

        if (lobby) {
            VisioUtils.disableLobbyCallButton();
        }

        navigator.mediaDevices.getUserMedia(constraints).then(stream => {
            MovimVisio.localStream = stream;

            if (lobby) {
                lobby.classList.add('configure');
            } else {
                MovimVisio.clear();
                return;
            }

            stream.getTracks().forEach(track => {
                if (lobby) {
                    VisioUtils.enableLobbyCallButton();
                }

                if (track.kind == 'audio') {
                    MovimVisio.localAudio.srcObject = stream;
                } else if (withVideo && track.kind == 'video') {
                    MovimVisio.localVideo.srcObject = stream;

                    if (lobby) {
                        let cameraPreview = lobby.querySelector('video#camera_preview');
                        cameraPreview.addEventListener('loadeddata', () => cameraPreview.play());
                        cameraPreview.srcObject = stream;
                        cameraPreview.disablePictureInPicture = true;
                    }

                }
            });

            VisioUtils.handleAudio();

            if (withVideo) {
                VisioUtils.enableScreenSharingButton();
            }

            navigator.mediaDevices.enumerateDevices().then(devices => Visio.gotDevices(withVideo, devices));
        });
    },

    gotDevices: function(withVideo, devicesInfo) {
        microphoneFound = false;
        cameraFound = false;

        let microphoneSelect = document.querySelector('select[name=default_microphone]');
        microphoneSelect.onchange = (e) => {
            localStorage.defaultMicrophone = e.target.value;
            Visio.getUserMedia(withVideo);
        };
        microphoneSelect.innerText = '';

        VisioUtils.handleAudio();

        let cameraSelect = document.querySelector('select[name=default_camera]');

        if (cameraSelect) {
            cameraSelect.addEventListener('change', e => {
                localStorage.defaultCamera = e.target.value;

                let cameraPreview = document.querySelector('video#camera_preview');

                if (cameraPreview.srcObject) {
                    cameraPreview.srcObject.getTracks().forEach(track => track.stop());
                }

                cameraPreview.srcObject = null;

                Visio.getUserMedia(withVideo);
            });
            cameraSelect.innerText = '';
        }

        for (const deviceInfo of devicesInfo) {
            if (deviceInfo.kind === 'audioinput') {
                const option = document.createElement('option');
                option.value = deviceInfo.deviceId;
                option.text = deviceInfo.label || `Microphone ${microphoneSelect.length + 1}`;

                if (deviceInfo.deviceId == localStorage.defaultMicrophone) {
                    option.selected = true;
                    microphoneFound = true;
                }

                microphoneSelect.appendChild(option);
            }

            if (withVideo && deviceInfo.kind === 'videoinput') {
                const option = document.createElement('option');
                option.value = deviceInfo.deviceId;
                option.text = deviceInfo.label || `Camera ${microphoneSelect.length + 1}`;

                if (deviceInfo.deviceId == localStorage.defaultCamera) {
                    option.selected = true;
                    cameraFound = true;
                }

                // Sometimes we can have two devices with the same id
                if (!cameraSelect.querySelector('option[value="' + deviceInfo.deviceId + '"]')) {
                    cameraSelect.appendChild(option);
                }
            }
        }

        if (microphoneFound == false) {
            localStorage.defaultMicrophone = microphoneSelect.value;
        }

        if (withVideo && cameraFound == false) {
            localStorage.defaultCamera = cameraSelect.value;
        }
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

        if (VisioUtils.audioContext) {
            VisioUtils.audioContext.close();
            VisioUtils.audioContext = null;
        }

        if (VisioUtils.remoteAudioContext) {
            VisioUtils.remoteAudioContext.close();
            VisioUtils.remoteAudioContext = null;
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
