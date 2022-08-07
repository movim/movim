var VisioConfig = {
    micMaxLevel: 0,
    audioContext: null,

    init: function () {
        navigator.mediaDevices.enumerateDevices().then(devices => VisioConfig.gotDevices(devices));
    },

    gotDevices: function (deviceInfos) {
        let microphoneSelect = document.querySelector('select[name=default_microphone]');
        microphoneSelect.addEventListener('change', VisioConfig.changeDefaultMicrophone);
        microphoneSelect.innerText = '';

        let cameraSelect = document.querySelector('select[name=default_camera]');
        cameraSelect.addEventListener('change', VisioConfig.changeDefaultCamera);
        cameraSelect.innerText = '';

        microphoneFound = false;
        cameraFound = false;

        for (const deviceInfo of deviceInfos) {
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

            if (deviceInfo.kind === 'videoinput') {
                const option = document.createElement('option');
                option.value = deviceInfo.deviceId;
                option.text = deviceInfo.label || `Camera ${microphoneSelect.length + 1}`;

                if (deviceInfo.deviceId == localStorage.defaultCamera) {
                    option.selected = true;
                    cameraFound = true;
                }

                cameraSelect.appendChild(option);
            }
        }

        if (microphoneFound == false) {
            localStorage.defaultMicrophone = microphoneSelect.value;
        }

        if (cameraFound == false) {
            localStorage.defaultCamera = cameraSelect.value;
        }

        VisioConfig.testMicrophone();
    },

    testMicrophone: function () {
        document.querySelectorAll('.level span').forEach(span => {
            span.classList.add('disabled');
        });

        if (localStorage.defaultMicrophone) {
            audioContraint = {
                deviceId: {
                    exact: localStorage.defaultMicrophone
                }
            }
        } else {
            return;
        }

        VisioConfig.micMaxLevel = 0;

        navigator.mediaDevices.getUserMedia({
            audio: audioContraint
        }).then(function (stream) {
            if (VisioConfig.audioContext) {
                VisioConfig.audioContext.close();
            }

            VisioConfig.audioContext = new AudioContext();
            let microphone = VisioConfig.audioContext.createMediaStreamSource(stream);
            var javascriptNode = VisioConfig.audioContext.createScriptProcessor(2048, 1, 1);
            let noMicSound = document.querySelector('#visioconfig_widget #no_mic_sound');

            microphone.connect(javascriptNode);
            javascriptNode.connect(VisioConfig.audioContext.destination);

            let isMuteStep = 0;

            javascriptNode.onaudioprocess = function(event) {
                var inpt = event.inputBuffer.getChannelData(0);
                var instant = 0.0;
                var sum = 0.0;

                for(var i = 0; i < inpt.length; ++i) {
                    sum += inpt[i] * inpt[i];
                }
                instant = Math.sqrt(sum / inpt.length);
                VisioConfig.micMaxLevel = Math.max(VisioConfig.micMaxLevel, instant);

                var base = (instant/VisioConfig.micMaxLevel);
                var level = (base > 0.01) ? base**.3 : 0;

                let step = 0;

                if (level == 0) {
                    isMuteStep++;
                } else {
                    isMuteStep = 0;
                }

                if (isMuteStep > 50) {
                    noMicSound.classList.remove('disabled');
                } else {
                    noMicSound.classList.add('disabled');

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
        })
        .catch(function (err) {
            console.error(err);
        });
    },

    testCamera: function() {
        if (localStorage.defaultCamera) {
            videoConstraint = {
                deviceId: {
                    exact: localStorage.defaultCamera
                }
            }
        } else {
            return;
        }

        navigator.mediaDevices.getUserMedia({
            video: videoConstraint
        }).then(function (stream) {
            let camera = document.querySelector('#camera_preview video');
            camera.srcObject = stream;
            camera.play();

            document.querySelector('#camera_preview').classList.add('enabled');
        })
        .catch(function (err) {
            console.error(err);
        });
    },

    changeDefaultMicrophone: function (event) {
        localStorage.defaultMicrophone = event.target.value;
        VisioConfig_ajaxDefaultMicrophoneChanged();
        VisioConfig.testMicrophone();
    },

    changeDefaultCamera: function (event) {
        localStorage.defaultCamera = event.target.value;
        VisioConfig_ajaxDefaultCameraChanged();
        VisioConfig.testCamera();
    }
}

MovimWebsocket.attach(() => {
    VisioConfig.init();
});
