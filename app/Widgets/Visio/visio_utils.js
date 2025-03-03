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

        var javascriptNode = VisioUtils.audioContext.createScriptProcessor(2048, 1, 1);
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

            var base = (instant / VisioUtils.maxLevel);
            var level = (base > 0.05) ? base ** .3 : 0;
            let step = 0;

            if (level == 0) {
                isMuteStep++;
            } else {
                isMuteStep = 0;
            }

            if (isMuteStep > 250) {
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
                document.querySelectorAll('.level span').forEach(span => {
                    if (step < Math.floor(level * 10)) {
                        span.classList.remove('disabled');
                    } else {
                        span.classList.add('disabled');
                    }

                    step++;
                });
            }

            mainButton.style.outlineColor = 'rgba(255, 255, 255, ' + level.toFixed(2) + ')';
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

    toggleMainButton: function () {
        button = document.getElementById('main');
        state = document.querySelector('p.state');

        i = button.querySelector('i');

        button.classList.remove('red', 'green', 'gray', 'orange', 'ring', 'blue');
        button.classList.add('disabled');

        if (MovimVisio.pc) {
            let length = MovimVisio.pc.getSenders().length;

            if (MovimVisio.pc.iceConnectionState != 'closed'
                && length > 0) {
                button.classList.remove('disabled');
            }

            button.onclick = function () { };

            if (length == 0) {
                button.classList.add('gray');
                i.innerText = 'more_horiz';
            } else if (MovimVisio.pc.iceConnectionState == 'new') {
                //if (MovimVisio.pc.iceGatheringState == 'gathering'
                //|| MovimVisio.pc.iceGatheringState == 'complete') {
                if (MovimVisio.calling) {
                    button.classList.add('orange');
                    i.className = 'material-symbols ring';
                    i.innerText = 'call';
                    state.innerText = MovimVisio.states.ringing;

                    button.onclick = function () { MovimJingles.terminateAll('cancel'); };
                } else {
                    button.classList.add('green');
                    button.classList.add('disabled');
                    i.innerText = 'call';
                }
            } else if (MovimVisio.pc.iceConnectionState == 'checking') {
                button.classList.add('blue');
                i.className = 'material-symbols disabled';
                i.innerText = 'more_horiz';
                state.innerText = MovimVisio.states.connecting;
            } else if (MovimVisio.pc.iceConnectionState == 'closed') {
                button.classList.add('gray');
                button.classList.remove('disabled');
                i.innerText = 'call_end';

                button.onclick = function () { MovimJingles.terminateAll(); };
            } else if (MovimVisio.pc.iceConnectionState == 'connected'
                || MovimVisio.pc.iceConnectionState == 'complete'
                || MovimVisio.pc.iceConnectionState == 'failed') {
                button.classList.add('red');
                i.className = 'material-symbols';
                i.innerText = 'call_end';

                if (MovimVisio.pc.iceConnectionState == 'failed') {
                    state.innerText = MovimVisio.states.failed;
                } else {
                    state.innerText = MovimVisio.states.in_call;
                }

                button.onclick = () => MovimJingles.terminateAll();
            }
        } else {
            button.classList.add('red');
            i.className = 'material-symbols';
            i.innerText = 'close';

            button.onclick = () => MovimJingles.terminateAll();
        }
    },

    enableScreenSharingButton: function () {
        document.querySelector('#screen_sharing').classList.add('enabled');
    },

    enableSwitchCameraButton: function () {
        MovimVisio.switchCamera.classList.remove('disabled');
    },

    disableSwitchCameraButton: function () {
        MovimVisio.switchCamera.classList.add('disabled');
    },

    enableLobbyCallButton: function () {
        document.querySelector('#lobby_start').classList.remove('disabled');
    },

    disableLobbyCallButton: function () {
        document.querySelector('#lobby_start').classList.add('disabled');
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
                    audio: false
                });

                MovimVisio.screenSharing.classList.add('sharing');
                VisioUtils.disableSwitchCameraButton();
                button.innerText = 'stop_screen_share';

                MovimVisio.gotScreen();
            } catch (err) {
                console.error("Error: " + err);
            }
        } else {
            MovimVisio.screenSharing.srcObject.getTracks().forEach(track => track.stop());
            MovimVisio.screenSharing.srcObject = null;
            MovimVisio.screenSharing.classList.remove('sharing');
            VisioUtils.enableSwitchCameraButton();

            button.innerText = 'screen_share';

            MovimVisio.gotQuickStream();
        }
    },

    // TODO Use MovimVisio.getDevices
    /*switchCameraInCall: function () {
        MovimVisio.videoSelect = document.querySelector('#visio select#visio_source');
        MovimVisio.switchCamera = document.querySelector("#visio #switch_camera");

        navigator.mediaDevices.enumerateDevices().then(devicesInfo => {
            MovimVisio.videoSelect.innerText = '';

            for (const deviceInfo of devicesInfo) {
                if (deviceInfo.kind === 'videoinput') {
                    const option = document.createElement('option');
                    option.value = deviceInfo.deviceId;
                    option.text = deviceInfo.label || 'Camera ' + MovimVisio.videoSelect.length + 1;

                    if (!Visio.videoSelect.querySelector('option[value="' + deviceInfo.deviceId + '"]')) {
                        MovimVisio.videoSelect.appendChild(option);
                    }
                }
            }

            if (Visio.videoSelect.options.length >= 2) {
                MovimVisio.switchCamera.classList.add('enabled');
            }
        });

        MovimVisio.switchCamera.onclick = () => {
            MovimVisio.videoSelect.selectedIndex++;

            if (Visio.videoSelect.selectedIndex == -1) {
                MovimVisio.videoSelect.selectedIndex++;
            }

            Toast.send(Visio.videoSelect.options[Visio.videoSelect.selectedIndex].label);

            var constraints = {
                video: true
            };

            constraints.video = {
                deviceId: MovimVisio.videoSelect.options[Visio.videoSelect.selectedIndex].value,
                width: { ideal: 4096 },
                height: { ideal: 4096 }
            };

            MovimVisio.localVideo.srcObject = null;

            VisioUtils.disableSwitchCameraButton();

            var videoTrack = MovimVisio.pc.getSenders().find(rtc => rtc.track && rtc.track.kind == 'video');
            if (videoTrack) videoTrack.track.stop();

            navigator.mediaDevices.getUserMedia(constraints).then(stream => {
                stream.getTracks().forEach(track => {
                    MovimVisio.pc.addTrack(track, stream);

                    if (track.kind == 'video') {
                        MovimVisio.localVideo.srcObject = stream;
                        localStorage.setItem('defaultCamera', track.getSettings().deviceId);
                    }
                });

                VisioUtils.enableSwitchCameraButton();
                var cameraIcon = document.querySelector('#toggle_video i');
                cameraIcon.innerText = 'videocam';

                VisioUtils.pcReplaceTrack(stream);
                VisioUtils.enableScreenSharingButton();
                VisioUtils.toggleMainButton();
            }, logError);
        };
    },*/
}
