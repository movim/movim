var VisioUtils = {
    maxLevel: 0,
    remoteMaxLevel: 0,
    audioContext: null,
    remoteAudioContext: null,

    handleAudio: function () {
        if (VisioUtils.audioContext) {
            VisioUtils.audioContext.close();
            VisioUtils.audioContext = null;
        }

        VisioUtils.audioContext = new AudioContext();

        try {
            var microphone = VisioUtils.audioContext.createMediaStreamSource(
                MovimVisio.localAudio.srcObject
            );
        } catch (error) {
            MovimUtils.logError(error);
            return;
        }

        var javascriptNode = VisioUtils.audioContext.createScriptProcessor(128 * 64, 1, 1);
        var icon = document.querySelector('#toggle_audio i');
        var mainButton = document.getElementById('main');
        icon.innerText = 'mic';
        let isMuteStep = 251;
        var noMicSound = document.querySelector('#no_mic_sound');
        var defaultMicrophone = document.querySelector('#default_microphone');

        if (defaultMicrophone) {
            defaultMicrophone.classList.add('muted');
        }

        microphone.connect(javascriptNode);
        javascriptNode.connect(VisioUtils.audioContext.destination);

        javascriptNode.onaudioprocess = function (event) {
            var inpt = event.inputBuffer.getChannelData(0);
            var instant = 0.0;
            var sum = 0.0;

            for (var i = 0; i < inpt.length; ++i) {
                sum += inpt[i] * inpt[i];
            }

            instant = Math.sqrt(sum / inpt.length);
            VisioUtils.maxLevel = Math.max(VisioUtils.maxLevel, instant);

            if (VisioUtils.maxLevel <= 0.005) VisioUtils.maxLevel = 0.005;

            var base = (instant / VisioUtils.maxLevel);
            var level = (base > 0.05) ? base ** .3 : 0;
            let step = 0;

            if (level == 0) {
                isMuteStep++;
            } else {
                isMuteStep = 0;
            }

            if (isMuteStep > 32) {
                if (noMicSound) {
                    noMicSound.classList.remove('disabled');
                }

                if (defaultMicrophone) {
                    defaultMicrophone.classList.add('muted');
                }
            } else {
                if (noMicSound) {
                    noMicSound.classList.add('disabled');
                }

                if (defaultMicrophone) {
                    defaultMicrophone.classList.remove('muted');
                }

                // Lobby level
                if (isMuteStep <= 5) {
                    document.querySelectorAll('.level span').forEach(span => {
                        if (step < Math.floor(level * 10)) {
                            span.classList.remove('disabled');
                        } else {
                            span.classList.add('disabled');
                        }

                        step++;
                    });
                }
            }

            if (isMuteStep <= 5) {
                mainButton.style.outlineColor = 'rgba(255, 255, 255, ' + level.toFixed(2) + ')';
            }
        }
    },

    toggleFullScreen: function () {
        var button = document.querySelector('#toggle_fullscreen i');

        if (!document.fullscreenElement) {
            if (document.querySelector('#visio').requestFullscreen) {
                document.querySelector('#visio').requestFullscreen();
            }

            button.innerText = 'fullscreen_exit';
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }

            button.innerText = 'fullscreen';
        }
    },

    toggleMode: function () {
        var button = document.querySelector('#toggle_mode i');
        let participants = document.querySelector('#participants');

        if (button.innerText == 'tile_small') {
            participants.classList.add('active');
            button.innerHTML = 'tile_large';
        } else {
            participants.classList.remove('active');
            button.innerHTML = 'tile_small';
        }
    },

    toggleAudio: function () {
        var button = document.querySelector('#toggle_audio i');

        if (button.innerText == 'mic_off') {
            MovimJingles.enableAudio(true);
            button.innerText = 'mic';
        } else {
            MovimJingles.enableAudio(false);
            button.innerText = 'mic_off';
        }
    },

    toggleVideo: function () {
        var button = document.querySelector('#toggle_video i');

        if (button.innerText == 'videocam_off') {
            MovimJingles.enableVideo(true);
            document.querySelector('#local_video').classList.remove('video_off');
            button.innerText = 'videocam';
        } else {
            MovimJingles.enableVideo(false);
            document.querySelector('#local_video').classList.add('video_off');
            button.innerText = 'videocam_off';
        }
    },

    switchChat: function () {
        let visio = document.querySelector('#visio');

        if (visio.dataset.jid) {
            Search.chat(visio.dataset.jid, (visio.dataset.muji == 'true'));
        }
    },

    toggleDtmf: function () {
        document.querySelector('#visio #dtmf').classList.toggle('hide');
    },

    insertDtmf: function (s) {
        VisioDTMF.pressButton(s);
        setTimeout(() => VisioDTMF.stop(), 100);

        var insert = (s == '*') ? '🞳' : s;
        document.querySelector('#dtmf p.dtmf span').innerText += insert;

        MovimJingles.insertDtmf(s);
    },

    clearDtMf: function () {
        document.querySelector('#dtmf p.dtmf span').innerText = '';
    },

    enableScreenSharingButton: function () {
        document.querySelector('#screen_sharing').classList.add('enabled');
    },

    disableSwitchCameraButton: function () {
        MovimVisio.switchCamera.classList.add('disabled');
    },

    enableLobbyCallButton: function () {
        if (document.querySelector('#lobby_start')) {
            document.querySelector('#lobby_start').classList.remove('disabled');
        }
    },

    disableLobbyCallButton: function () {
        if (document.querySelector('#lobby_start')) {
            document.querySelector('#lobby_start').classList.add('disabled');
        }
    },

    toggleScreenSharing: async function () {
        MovimVisio.switchCamera = document.querySelector("#visio #switch_camera");

        var button = document.querySelector('#screen_sharing i');
        if (MovimVisio.screenSharing.srcObject == null) {
            try {
                MovimVisio.screenSharing.srcObject = await navigator.mediaDevices.getDisplayMedia({
                    video: {
                        cursor: "always"
                    },
                    audio: true
                });

                MovimVisio.screenSharing.classList.add('sharing');
                VisioUtils.disableSwitchCameraButton();
                button.innerText = 'stop_screen_share';

                MovimJingles.enableScreenSharing();
            } catch (err) {
                console.error("Error: " + err);
            }
            return;
        } else {
            VisioUtils.disableScreenSharing();
        }
    },

    disableScreenSharing: function () {
        MovimJingles.disableScreenSharing();

        if (MovimVisio.screenSharing && MovimVisio.screenSharing.srcObject) {
            MovimVisio.screenSharing.srcObject.getTracks().forEach(track => track.stop());
            MovimVisio.screenSharing.srcObject = null;

            MovimVisio.screenSharing.classList.remove('sharing');
            MovimVisio.switchCamera.classList.remove('disabled');
        }

        if (button = document.querySelector('#screen_sharing i')) {
            button.innerText = 'screen_share';
        }
    },

    getConstraints: function (withVideo) {
        const cpuCores = navigator.hardwareConcurrency || 2;
        var constraints = {
            audio: true,
            video: false,
        };

        if (withVideo) {
            if (cpuCores <= 2) {
                constraints.video = {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    frameRate: { max: 15, ideal: 15 }
                };
            } else if (cpuCores <= 4) {
                constraints.video = {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    frameRate: { max: 30, ideal: 24 }
                };
            } else {
                constraints.video = {
                    width: { ideal: 1920 },
                    height: { ideal: 1080 },
                    frameRate: { max: 30, ideal: 30 }
                };
            }

            constraints.video.facingMode = 'user';

            if (localStorage.defaultCamera) {
                constraints.video.deviceId = localStorage.defaultCamera
            }
        }

        if (localStorage.defaultMicrophone) {
            constraints.audio = {
                deviceId: localStorage.defaultMicrophone
            }
        }

        return constraints;
    },

    setVideoCodecPreferences: function (transceiver) {
        let codecs = RTCRtpReceiver.getCapabilities('video').codecs;

        if (transceiver.setCodecPreferences != undefined) {
            let preferredOrder = [/*'video/AV1',*/ 'video/H264', 'video/VP8', 'video/VP9'];
            codecs.sort((a, b) => {
                const indexA = preferredOrder.indexOf(a.mimeType);
                const indexB = preferredOrder.indexOf(b.mimeType);
                const orderA = indexA >= 0 ? indexA : Number.MAX_VALUE;
                const orderB = indexB >= 0 ? indexB : Number.MAX_VALUE;
                return orderA - orderB;
            });
            transceiver.setCodecPreferences(codecs);
        }
    },

    adaptToNetworkCondition: async function (peerConnection) {
        const stats = await peerConnection.getStats();
        let packetLossRate = 0;
        let rtt = 0;

        stats.forEach(stat => {
            if (stat.type === 'outbound-rtp' && stat.kind === 'video') {
                if (stat.packetsSent && stat.packetsLost) {
                    packetLossRate = stat.packetsLost / stat.packetsSent;
                }
            }

            if (stat.type === 'remote-inbound-rtp') {
                rtt = stat.roundTripTime;
            }
        });

        let networkIcon = document.querySelector('#network_condition');
        networkIcon.classList.remove('excellent', 'good', 'bad');

        peerConnection.getSenders().filter(s =>
            s.track && s.track.kind === 'video'
        ).forEach(sender => {
            const parameters = sender.getParameters();

            // Don't exceed original encoding
            if (!parameters.encodings || parameters.encodings.length === 0) {
                parameters.encodings = [{}];
            }

            if (packetLossRate > 0.1 || rtt > 0.6) {
                networkIcon.classList.add('bad');
                parameters.encodings[0].maxBitrate = 250000; // 250 kbps
                parameters.encodings[0].scaleResolutionDownBy = 4; // 1/4 resolution
            } else if (packetLossRate > 0.05 || rtt > 0.3) {
                networkIcon.classList.add('good');
                parameters.encodings[0].maxBitrate = 500000; // 500 kbps
                parameters.encodings[0].scaleResolutionDownBy = 2; // 1/2 resolution
            } else {
                networkIcon.classList.add('excellent');
                parameters.encodings[0].maxBitrate = 1500000; // 1.5 Mbps
                parameters.encodings[0].scaleResolutionDownBy = 1; // Full resolution
            }

            sender.setParameters(parameters);
        });
    },

    cancelLobby: function (fullJid, id) {
        if (fullJid && id) {
            Visio_ajaxReject(fullJid, id);
        }

        Visio_ajaxClear(); Dialog_ajaxClear(); Notif.snackbarClear();
    }
}
