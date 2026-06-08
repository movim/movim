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
    midRegex: 'a=mid:(.+)',

    mouseMovement: null,
    mouseMovementTimeout: 5000,

    load: function () {
        MovimVisio.localVideo = document.getElementById('local_video');

        // Disable the video toggle by default
        MovimVisio.localVideo.classList.add('video_off');

        MovimVisio.localVideo.addEventListener('loadeddata', () => {
            MovimVisio.localVideo.play()
        });

        MovimVisio.screenSharing = document.getElementById('screen_sharing_video');
        MovimVisio.localAudio = document.getElementById('local_audio');
    },

    init: function (fullJid, jid, id, withVideo, isMuji, contactName, contactAvatarUrl) {
        Visio_ajaxHttpPrepareInfo(jid, isMuji);

        MovimVisio.id = id;

        // Set a lock for the current browser (in case there's several others opened)
        localStorage.setItem('callId', id);
        localStorage.setItem('callJid', jid);

        let visio = document.querySelector('#visio');
        delete visio.dataset.type;
        visio.dataset.jid = jid;
        visio.dataset.muji = isMuji ? 'true' : 'false';

        if (isMuji == true) {
            MovimVisio.mujiPublish(true);
            MovimVisio.activeSpeakerIntervalId = setInterval(MovimJingles.checkActiveSpeaker, 1000);
        } else {
            MovimJingles.initSession(jid, fullJid, id, contactName, contactAvatarUrl);

            if (MovimVisio.id) {
                // Called
                Visio_ajaxProceed(fullJid, MovimVisio.id);
            } else {
                // Calling
                MovimVisio.id = crypto.randomUUID();
                Visio_ajaxPropose(jid, MovimVisio.id, withVideo);
            }
        }

        visio.classList.add('movements');

        Notif.setCallStatus(MovimVisio.states.in_call);

        visio.addEventListener('mousemove', e => {
            clearTimeout(MovimVisio.mouseMovement);
            visio.classList.add('movements');

            MovimVisio.mouseMovement = setTimeout(() => {
                visio.classList.remove('movements');
            }, MovimVisio.mouseMovementTimeout);
        })

        if (typeof navigator.mediaDevices.getDisplayMedia == 'undefined') {
            document.querySelector('#screen_sharing').classList.add('hide');
        }
    },

    mujiPublish: function (init) {
        let pc = new RTCPeerConnection({ 'iceServers': MovimVisio.services });

        /**
         * Ugly heuristic because it is not possible to match the SDP mids with the tranceiver sender track ids...
         */
        let id = 0;

        MovimVisio.localStream.getTracks().forEach(track => {
            pc.addTrack(track, MovimVisio.localStream);
            id++;
        });

        let screenIds = [];

        if (MovimVisio.screenSharing.srcObject) {
            MovimVisio.screenSharing.srcObject.getTracks().forEach(track => {
                pc.addTrack(track);
                screenIds.push(id);
                id++;
            });
        }

        pc.createOffer().then(function (offer) {
            if (MovimVisio.screenSharing.srcObject) {
                // XEP-0507: Jingle Content Category
                newMedias = offer.sdp.split('m=').map(media => {
                    let mid = media.match(MovimVisio.midRegex);
                    if (mid != null) {
                        if (screenIds.includes(parseInt(mid[1]))) {
                            return media + 'a=content:slides' + "\n";
                        }
                    }

                    return media;
                });

                localDescription = newMedias.join('m=');
                offer.sdp = localDescription;
            }

            Visio_ajaxMujiPublish(MovimVisio.id, offer, init);
            pc.close();
        });
    },

    setStates: function (states) {
        MovimVisio.states = states;
    },

    setServices: function (services) {
        MovimVisio.services = services;
    },

    getUserMedia: function (withVideo) {
        MovimVisio.load();

        let lobby = document.querySelector('#visio_lobby');

        if (lobby) {
            VisioUtils.disableLobbyCallButton();
        }

        MovimTpl.loadingPage();

        return navigator.mediaDevices.getUserMedia(VisioUtils.getConstraints(withVideo)).then(stream => {
            MovimTpl.finishedPage();

            MovimVisio.localStream = stream;

            if (lobby) {
                lobby.classList.add('configure');
            } else if (MovimVisio.localAudio.srcObject == null) {
                Visio_ajaxClear();
                return;
            }

            stream.getTracks().forEach(track => {
                if (lobby) {
                    VisioUtils.enableLobbyCallButton();
                }

                if (track.kind == 'audio' && MovimVisio.localAudio && MovimVisio.localAudio.srcObject == null /* In case we're just adding the video */) {
                    MovimVisio.localAudio.srcObject = stream;
                } else if (withVideo && track.kind === 'video') {
                    MovimVisio.localVideo.srcObject = stream;

                    // Toggle the video on
                    MovimVisio.localVideo.classList.remove('video_off');
                    document.querySelector('#toggle_video i').innerText = 'videocam';

                    if (lobby) {
                        let cameraPreview = lobby.querySelector('video#camera_preview');
                        cameraPreview.addEventListener('loadeddata', () => cameraPreview.play());
                        cameraPreview.srcObject = stream;
                        cameraPreview.disablePictureInPicture = true;
                    }

                }
            });

            VisioUtils.handleAudio();

            if (lobby) {
                navigator.mediaDevices.enumerateDevices().then(devices => MovimVisio.gotDevices(withVideo, devices));
            }
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

    clear: function () {
        MovimTpl.finishedPage();

        MovimVisio.id = null;

        Notif.setCallStatus(null);

        clearInterval(MovimVisio.activeSpeakerIntervalId);

        let visio = document.querySelector('#visio');
        //delete visio.dataset.type;
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

            MovimVisio.localAudio.srcObject = null;
        }

        if (MovimVisio.localVideo) {
            let stream = MovimVisio.localVideo.srcObject;

            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }

            MovimVisio.localVideo.srcObject = null;
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

        if ((parts.page == 'chat' && parts.params[0] == jid)
            || (parts.page == 'space' && parts.params[2] == jid)) {
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

    },

    callStop: function (id, jid) {
        localStorage.removeItem('callId');
        localStorage.removeItem('callJid');
    }
}

if (typeof Visio_ajaxHttpGetStates == 'function') {
    Visio_ajaxHttpGetStates();
}

MovimWebsocket.attach(() => {
    if (MovimVisio.services.length == 0 && typeof Visio_ajaxResolveServices == 'function') {
        Visio_ajaxResolveServices();
        Visio_ajaxCheckStatus(localStorage.getItem('callId'), localStorage.getItem('callJid'));
    }
});
