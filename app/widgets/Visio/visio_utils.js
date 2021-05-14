var VisioUtils = {
    maxLevel: 0,
    remoteMaxLevel: 0,
    audioContext: null,
    remoteAudioContext: null,

    handleAudio: function() {
        VisioUtils.audioContext = new AudioContext();

        try {
            var microphone = VisioUtils.audioContext.createMediaStreamSource(
                Visio.withVideo
                ? Visio.localVideo.srcObject
                : Visio.localAudio.srcObject
            );
        } catch (error) {
            logError(error);
            return;
        }

        var javascriptNode = VisioUtils.audioContext.createScriptProcessor(2048, 1, 1);
        var icon = document.querySelector('#toggle_audio i');
        icon.innerText = 'mic';

        microphone.connect(javascriptNode);
        javascriptNode.connect(VisioUtils.audioContext.destination);
        javascriptNode.onaudioprocess = function(event) {
            var inpt = event.inputBuffer.getChannelData(0);
            var instant = 0.0;
            var sum = 0.0;

            for(var i = 0; i < inpt.length; ++i) {
                sum += inpt[i] * inpt[i];
            }

            instant = Math.sqrt(sum / inpt.length);
            VisioUtils.maxLevel = Math.max(VisioUtils.maxLevel, instant);

            var level = Math.log2((instant/VisioUtils.maxLevel)+1);

            if (level < 0.02) {
                icon.style.color = 'rgb(255, 255, 255, 1)';
            } else {
                var inverse = 255-(level.toPrecision(2)*255);
                icon.style.color = 'rgb(' + inverse + ', 255, ' + inverse + ')';
            }
        }
    },

    handleRemoteAudio: function() {
        VisioUtils.remoteAudioContext = new AudioContext();

        try {
            var remoteMicrophone = VisioUtils.remoteAudioContext.createMediaStreamSource(
                Visio.withVideo
                ? Visio.remoteVideo.srcObject
                : Visio.remoteAudio.srcObject
            );
        } catch (error) {
            logError(error);
            return;
        }

        var remoteJavascriptNode = VisioUtils.remoteAudioContext.createScriptProcessor(2048, 1, 1);
        var remoteMeter = document.querySelector('#visio #remote_level');

        remoteMicrophone.connect(remoteJavascriptNode);
        remoteJavascriptNode.connect(VisioUtils.remoteAudioContext.destination);
        remoteJavascriptNode.onaudioprocess = function(event) {
            var inpt = event.inputBuffer.getChannelData(0);
            var instant = 0.0;
            var sum = 0.0;

            for(var i = 0; i < inpt.length; ++i) {
                sum += inpt[i] * inpt[i];
            }

            instant = Math.sqrt(sum / inpt.length);
            VisioUtils.remoteMaxLevel = Math.max(VisioUtils.remoteMaxLevel, instant);

            var level = Math.log2((instant/VisioUtils.remoteMaxLevel)+1);
            if (level < 0.02) {
                level = 0;
                VisioUtils.remoteMaxLevel = 0;
            }

            remoteMeter.style.borderColor = 'rgba(255, 255, 255, ' + level + ')';
        }
    },

    toggleFullScreen: function() {
        var button = document.querySelector('#toggle_fullscreen i');

        if (!document.fullscreenElement) {
            if (document.body.requestFullscreen) {
                document.body.requestFullscreen();
            }

            button.innerText = 'fullscreen_exit';
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }

            button.innerText = 'fullscreen';
        }
    },

    toggleAudio: function() {
        var button = document.querySelector('#toggle_audio i');
        var rtc = Visio.pc.getSenders().find(rtc => rtc.track && rtc.track.kind == 'audio');

        if (rtc && rtc.track.enabled == 1) {
            rtc.track.enabled = 0;
            button.innerText = 'mic_off';
            Visio_ajaxUnmute(Visio.from, Visio.id, 'voice');
        } else if (rtc) {
            rtc.track.enabled = 1;
            button.innerText = 'mic';
            Visio_ajaxMute(Visio.from, Visio.id, 'voice');
        }
    },

    toggleVideo: function() {
        var button = document.querySelector('#toggle_video i');
        var rtc = Visio.pc.getSenders().find(rtc => rtc.track && rtc.track.kind == 'video');

        if (rtc) {
            if (rtc.track.enabled == 1) {
                rtc.track.enabled = 0;
                button.innerText = 'videocam_off';
                document.querySelector('#video').classList.add('muted');
                Visio_ajaxUnmute(Visio.from, Visio.id, 'webcam');
            } else {
                rtc.track.enabled = 1;
                button.innerText = 'videocam';
                document.querySelector('#video').classList.remove('muted');
                Visio_ajaxMute(Visio.from, Visio.id, 'webcam');
            }
        }
    },

    setRemoteAudioState: function(icon) {
        var voice = document.querySelector('#remote_state i.voice');
        voice.innerHTML = icon;
    },

    setRemoteVideoState: function(icon) {
        var webcam = document.querySelector('#remote_state i.webcam');
        webcam.innerHTML = icon;
    },

    toggleMainButton: function() {
        button = document.getElementById('main');
        state = document.querySelector('p.state');

        i = button.querySelector('i');

        button.classList.remove('red', 'green', 'gray', 'orange', 'ring', 'blue');
        button.classList.add('disabled');

        if (Visio.pc) {
            let length = Visio.pc.getSenders().length;

            if (Visio.pc.iceConnectionState != 'closed'
            && length > 0) {
                button.classList.remove('disabled');
            }

            button.onclick = function() {};

            if (length == 0) {
                button.classList.add('gray');
                i.innerText = 'more_horiz';
            } else if (Visio.pc.iceConnectionState == 'new') {
                //if (Visio.pc.iceGatheringState == 'gathering'
                //|| Visio.pc.iceGatheringState == 'complete') {
                if (Visio.calling) {
                    button.classList.add('orange');
                    i.className = 'material-icons ring';
                    i.innerText = 'call';
                    state.innerText = Visio.states.ringing;

                    button.onclick = function() { Visio.goodbye('cancel'); };
                } else {
                    button.classList.add('green');
                    button.classList.add('disabled');
                    i.innerText = 'call';
                }
            } else if (Visio.pc.iceConnectionState == 'checking') {
                button.classList.add('blue');
                i.className = 'material-icons disabled';
                i.innerText = 'more_horiz';
                state.innerText = Visio.states.connecting;
            } else if (Visio.pc.iceConnectionState == 'closed') {
                button.classList.add('gray');
                button.classList.remove('disabled');
                i.innerText = 'call_end';

                button.onclick = function() { Visio.goodbye(); };
            } else if (Visio.pc.iceConnectionState == 'connected'
                   || Visio.pc.iceConnectionState == 'complete'
                   || Visio.pc.iceConnectionState == 'failed') {
                button.classList.add('red');
                i.className = 'material-icons';
                i.innerText = 'call_end';

                if (Visio.pc.iceConnectionState == 'failed') {
                    state.innerText = Visio.states.failed;
                } else {
                    state.innerText = Visio.states.in_call;
                }

                button.onclick = () => Visio.goodbye();
            }
        }
    },

    enableScreenSharingButton: function() {
        document.querySelector('#screen_sharing').classList.add('enabled');
    },

    toggleScreenSharing: async function() {
        var button = document.querySelector('#screen_sharing i');
        if (Visio.screenSharing.srcObject == null) {
            try {
                Visio.screenSharing.srcObject = await navigator.mediaDevices.getDisplayMedia({
                    video: {
                        cursor: "always"
                    },
                    audio: false
                });

                Visio.screenSharing.classList.add('sharing');
                Visio.switchCamera.classList.add('disabled');
                button.innerText = 'stop_screen_share';

                Visio.gotScreen();
            } catch(err) {
                console.error("Error: " + err);
            }
        } else {
            Visio.screenSharing.srcObject.getTracks().forEach(track => track.stop());
            Visio.screenSharing.srcObject = null;
            Visio.screenSharing.classList.remove('sharing');
            Visio.switchCamera.classList.remove('disabled');

            button.innerText = 'screen_share';

            Visio.gotQuickStream();
        }
    },

    switchCameraSetup: function() {
        Visio.videoSelect = document.querySelector('#visio select#visio_source');
        navigator.mediaDevices.enumerateDevices().then(devices => VisioUtils.gotDevices(devices));

        Visio.switchCamera = document.querySelector("#visio #switch_camera");
        Visio.switchCamera.onclick = () => {
            Visio.videoSelect.selectedIndex++;

            // No empty selection
            if (Visio.videoSelect.selectedIndex == -1) {
                Visio.videoSelect.selectedIndex++;
            }

            Toast.send(Visio.videoSelect.options[Visio.videoSelect.selectedIndex].label);
            Visio.getStream();
        };
    },

    pcReplaceTrack: function(stream) {
        let videoTrack = stream.getVideoTracks()[0];
        var sender = Visio.pc.getSenders().find(s => s.track && s.track.kind == videoTrack.kind);

        if (sender) {
            sender.replaceTrack(videoTrack);
        }
    },

    gotDevices: function(deviceInfos) {
        Visio.videoSelect.innerText = '';

        for (const deviceInfo of deviceInfos) {
            if (deviceInfo.kind === 'videoinput') {
                const option = document.createElement('option');
                option.value = deviceInfo.deviceId;
                option.text = deviceInfo.label || `Camera ${videoSelect.length + 1}`;

                Visio.videoSelect.appendChild(option);
            }
        }

        if (Visio.videoSelect.options.length >= 2) {
            document.querySelector("#visio #switch_camera").classList.add('enabled');
        }
    }
}
