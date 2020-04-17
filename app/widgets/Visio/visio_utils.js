var VisioUtils = {
    max_level_L: 0,
    old_level_L: 1,
    remote_max_level_L: 0,
    remote_old_level_L: 1,
    audioContext: null,
    remoteAudioContext: null,

    handleAudio: function() {
        VisioUtils.audioContext = new AudioContext();

        var microphone = VisioUtils.audioContext.createMediaStreamSource(
            Visio.withVideo
                ? Visio.localVideo.srcObject
                : Visio.localAudio.srcObject
        );

        // Local microphone
        var javascriptNode = VisioUtils.audioContext.createScriptProcessor(256, 1, 1);

        var icon = document.querySelector('#toggle_audio i');

        microphone.connect(javascriptNode);
        javascriptNode.connect(VisioUtils.audioContext.destination);
        javascriptNode.onaudioprocess = function(event) {
            var inpt_L = event.inputBuffer.getChannelData(0);
            var instant_L = 0.0;

            var sum_L = 0.0;

            for(var i = 0; i < inpt_L.length; ++i) {
                sum_L += inpt_L[i] * inpt_L[i];
            }

            instant_L = Math.sqrt(sum_L / inpt_L.length);
            VisioUtils.max_level_L = Math.max(VisioUtils.max_level_L, instant_L);
            instant_L = Math.max(instant_L, VisioUtils.old_level_L -0.0005 );
            VisioUtils.old_level_L = instant_L;

            var level = (instant_L/VisioUtils.max_level_L);
            if (level < 0.1) {
                icon.style.color = 'white';
            } else {
                var inverse = 255-(level*255);
                icon.style.color = 'rgb('+inverse+', 255, '+inverse+')';
            }
        }
    },

    handleRemoteAudio: function() {
        VisioUtils.remoteAudioContext = new AudioContext();

        var remoteMicrophone = VisioUtils.remoteAudioContext.createMediaStreamSource(
            Visio.withVideo
                ? Visio.remoteVideo.srcObject
                : Visio.remoteAudio.srcObject
        );

        // Remote microphone
        var remoteJavascriptNode = VisioUtils.remoteAudioContext.createScriptProcessor(256, 1, 1);

        var image = document.querySelector('#visio .infos img');

        remoteMicrophone.connect(remoteJavascriptNode);
        remoteJavascriptNode.connect(VisioUtils.remoteAudioContext.destination);
        remoteJavascriptNode.onaudioprocess = function(event) {
            var inpt_L = event.inputBuffer.getChannelData(0);
            var instant_L = 0.0;

            var sum_L = 0.0;

            for(var i = 0; i < inpt_L.length; ++i) {
                sum_L += inpt_L[i] * inpt_L[i];
            }

            instant_L = Math.sqrt(sum_L / inpt_L.length);
            VisioUtils.max_level_L = Math.max(VisioUtils.max_level_L, instant_L);
            instant_L = Math.max(instant_L, VisioUtils.old_level_L -0.0005 );
            VisioUtils.old_level_L = instant_L;

            var level = (instant_L/VisioUtils.max_level_L);
            if (level < 0.1) level = 0;

            image.style.borderColor = 'rgba(255, 255, 255, ' + level + ')';
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
        } else if (rtc) {
            rtc.track.enabled = 1;
            button.innerText = 'mic';
        }
    },

    toggleVideo: function() {
        var button = document.querySelector('#toggle_video i');
        var rtc = Visio.pc.getSenders().find(rtc => rtc.track && rtc.track.kind == 'video');

        if (rtc) {
            if (rtc.track.enabled == 1) {
                rtc.track.enabled = 0;
                button.innerText = 'videocam_off';
            } else {
                rtc.track.enabled = 1;
                button.innerText = 'videocam';
            }
        }
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

            Visio.gotStream();
        };
    },

    gotDevices: function(deviceInfos) {
        Visio.videoSelect.innerText = '';

        const ids = [];

        for (let i = 0; i !== deviceInfos.length; ++i) {
            const deviceInfo = deviceInfos[i];

            if (deviceInfo.kind === 'videoinput' && !ids.includes(deviceInfo.deviceId)) {
                const option = document.createElement('option');
                option.value = deviceInfo.deviceId;
                option.text = deviceInfo.label;
                Visio.videoSelect.add(option);
                ids.push(deviceInfo.deviceId);
            }
        }

        if (ids.length >= 2) {
            document.querySelector("#visio #switch_camera").classList.add('enabled');
        }
    }
}
