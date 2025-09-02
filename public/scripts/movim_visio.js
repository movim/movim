var MovimVisio = {
    id: null,

    calling: false,

    pc: null,

    states: null,
    services: [],

    localStream: null,
    localVideo: null,
    localAudio: null,
    screenSharing: null,

    observer: null,

    activeSpeakerIntervalId: null,

    bundleRegex: 'a=group:(\\S+) (.+)',

    load: function () {
        MovimVisio.localVideo = document.getElementById('local_video');
        MovimVisio.localVideo.addEventListener('loadeddata', () => {
            MovimVisio.localVideo.play()
        });

        MovimVisio.screenSharing = document.getElementById('screen_sharing_video');
        MovimVisio.localAudio = document.getElementById('local_audio');
    },

    init: function (fullJid, jid, id, withVideo, isMuji) {
        Visio_ajaxPrepare(jid);

        MovimVisio.id = id;

        let visio = document.querySelector('#visio');
        delete visio.dataset.type;
        visio.dataset.jid = jid;
        visio.dataset.type = (withVideo) ? 'video' : 'audio';
        visio.dataset.muji = isMuji ? 'true' : 'false';

        if (isMuji == true) {
            let pc = new RTCPeerConnection({ 'iceServers': MovimVisio.services });

            var constraints = {
                audio: true,
                video: false,
            };

            if (withVideo) {
                constraints.video = true;
            }

            navigator.mediaDevices.getUserMedia(constraints).then(stream => {
                stream.getTracks().forEach(track => {
                    pc.addTrack(track, stream);
                });

                pc.createOffer().then(function (offer) {
                    Visio_ajaxMujiInit(MovimVisio.id, offer);

                    pc.close();
                });
            });

            MovimVisio.activeSpeakerIntervalId = setInterval(MovimJingles.checkActiveSpeaker, 1000);
        } else {
            MovimJingles.initSession(jid, fullJid, id);

            if (MovimVisio.id) {
                // Called
                Visio_ajaxProceed(fullJid, MovimVisio.id);
            } else {
                // Calling
                MovimVisio.id = crypto.randomUUID();
                MovimVisio.calling = true; // TODO, remove me ?
                Visio_ajaxPropose(jid, MovimVisio.id, withVideo);
            }
        }

        Notif.setCallStatus(MovimVisio.states.in_call);
    },

    setStates: function (states) {
        MovimVisio.states = states;
    },

    setServices: function (services) {
        MovimVisio.services = services;
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

        MovimTpl.loadingPage();

        navigator.mediaDevices.getUserMedia(constraints).then(stream => {
            MovimTpl.finishedPage();

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
        }, (e) => {
            MovimTpl.finishedPage();
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

    goodbye: function (reason) {
        let visio = document.querySelector('#visio');
        Visio_ajaxGoodbye(visio.dataset.jid, this.id, reason);
    },

    clear: function () {
        MovimTpl.finishedPage();

        MovimVisio.id = null;

        Notif.setCallStatus(null);

        clearInterval(MovimVisio.activeSpeakerIntervalId);

        let visio = document.querySelector('#visio');
        delete visio.dataset.type;
        delete visio.dataset.jid;
        delete visio.dataset.muji;

        if (document.fullscreenElement) {
            document.exitFullscreen();
        }

        if (VisioUtils.audioContext) {
            VisioUtils.audioContext.close();
            VisioUtils.audioContext = null;
        }

        if (MovimVisio.localAudio) {
            let stream = MovimVisio.localAudio.srcObject;

            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
        }

        if (MovimVisio.localVideo) {
            let stream = MovimVisio.localVideo.srcObject;

            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
        }

        if (MovimVisio.localStream) {
            MovimVisio.localStream.getTracks().forEach(function (track) {
                track.stop();
            });
            MovimVisio.localStream = null;
        }

        VisioUtils.disableScreenSharing();

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
        Chat.scrollRestore();

        const callback = (mutationList, observer) => {
            if (!document.getElementById('visio')) {
                document.getElementById('endcommon').before(visio);
            }
        };

        MovimVisio.observer = new MutationObserver(callback);
        MovimVisio.observer.observe(body, { childList: true, subtree: true });
    }
}

Visio_ajaxHttpGetStates();

MovimWebsocket.attach(() => {
    if (MovimVisio.services.length == 0) {
        Visio_ajaxResolveServices();
        Visio_ajaxTryForceStop();
    }
});
