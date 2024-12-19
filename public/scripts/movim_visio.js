var MovimVisio = {
    id: null,
    withVideo: false,
    muji: false,

    pc: null,
    services: [],

    localStream: null,
    localVideo: null,
    localAudio: null,
    screenSharing: null,

    observer: null,

    load: function () {
        MovimVisio.localVideo = document.getElementById('local_video');
        MovimVisio.localVideo.addEventListener('loadeddata', () => {
            MovimVisio.localVideo.play()
        });

        MovimVisio.screenSharing = document.getElementById('screen_sharing_video');
        MovimVisio.localAudio = document.getElementById('local_audio');
    },

    gotQuickStream: function () {
        VisioUtils.pcReplaceTrack(MovimVisio.localVideo.srcObject);
    },

    gotScreen: function () {
        VisioUtils.pcReplaceTrack(MovimVisio.screenSharing.srcObject);
    },

    mujiInit: function () {
        let pc = new RTCPeerConnection({ 'iceServers': MovimVisio.services });
        pc.createOffer().then(function (offer) {
            VisioUtils.toggleMainButton();
            Visio_ajaxMujiInit(MovimVisio.id, offer);
        });
    },

    getUserMedia: function (withVideo) {
        var constraints = {
            audio: true,
            video: false,
        };

        if (withVideo) {
            constraints.video = {
                facingMode: 'user',
                width: { ideal: 1920 },
                height: { ideal: 1920 }
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

            navigator.mediaDevices.enumerateDevices().then(devices => MovimVisio.gotDevices(withVideo, devices));
        });
    },

    gotDevices: function (withVideo, devicesInfo) {
        microphoneFound = false;
        cameraFound = false;

        let microphoneSelect = document.querySelector('select[name=default_microphone]');
        microphoneSelect.onchange = (e) => {
            localStorage.defaultMicrophone = e.target.value;
            MovimVisio.getUserMedia(withVideo);
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

                MovimVisio.getUserMedia(withVideo);
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

    clear: function () {
        MovimVisio.id = null;

        if (MovimVisio.localAudio) {
            let localStream = MovimVisio.localAudio.srcObject;

            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localStream = null;
            }
        }

        if (MovimVisio.localVideo) {
            let localStream = MovimVisio.localVideo.srcObject;

            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localStream = null;
            }
        }

        if (MovimVisio.localStream) {
            MovimVisio.localStream.getTracks().forEach(function (track) {
                track.stop();
            });
            MovimVisio.localStream = null;
        }

        MovimVisio.localAudio = null;
        MovimVisio.localVideo = null;
        MovimVisio.screenSharing = null;
    },

    moveToChat: function (jid) {
        if (MovimVisio.observer != null) {
            MovimVisio.observer.disconnect();
        }

        var parts = MovimUtils.urlParts();
        if (parts.page != 'chat' || parts.params[0] != jid) {
            return;
        }

        const visio = document.getElementById('visio');
        const body = document.body;

        document.querySelector('#chat_widget header').after(visio);

        const callback = (mutationList, observer) => {
            if (!document.getElementById('visio')) {
                document.getElementById('endcommon').before(visio);
            }
        };

        MovimVisio.observer = new MutationObserver(callback);
        MovimVisio.observer.observe(body, { childList: true, subtree: true });
    }
}
