var Dictaphone = {
    mediaRecorder: null,
    audio: null,
    progressBar: null,
    playPause: null,
    audioUpload: null,
    timer: null,
    timerInterval: null,
    recordTimeMs: 0,
    recordTimerStart: null,
    chunks: [],
    audioStream: null,

    toggle: function () {
        let classList = document.querySelector('#dictaphone_widget').classList;
        if (classList.contains('show')) {
            classList.remove('show');
            Dictaphone.clear();
        } else {
            classList.add('show');
            Chat.scrollTotally();
        }
    },

    init: function () {
        Dictaphone.audio = document.querySelector('#dictaphone_widget audio');
        Dictaphone.progressBar = document.querySelector('#dictaphone_widget input[type=range]');
        Dictaphone.playPause = document.querySelector('#dictaphone #play_pause');
        Dictaphone.audioUpload = document.querySelector('#dictaphone #audio_upload');
        Dictaphone.timer = document.querySelector('#dictaphone_widget #timer');
        const record = document.querySelector('#dictaphone_widget #record');
        record.onmousedown = Dictaphone.record;
        record.onmouseup = Dictaphone.stop;

        Dictaphone.updateRecordTimer();
    },

    clear: function () {
        Dictaphone.audio.src = '';
        Dictaphone.playPause.classList.remove('enabled');
        Dictaphone.recordTimeMs = 0;
        Dictaphone.progressBar.value = 0;
        Dictaphone.updateRecordTimer();
    },

    record: function () {
        const constraints = { audio: true };

        if (localStorage.getItem('defaultMicrophone')) {
            constraints.audio = {
                deviceId: localStorage.getItem('defaultMicrophone')
            }
        }

        let mouseDownOnSlider = false;

        navigator.mediaDevices.getUserMedia(constraints).then(audioStream => {
            Dictaphone.audioStream = audioStream;

            Dictaphone.mediaRecorder = new MediaRecorder(audioStream, { mimeType: 'audio/webm; codecs=opus' });

            Dictaphone.mediaRecorder.ondataavailable = function (e) {
                Dictaphone.chunks.push(e.data);
            }

            Dictaphone.mediaRecorder.onstart = function (e) {
                Dictaphone.clear();

                Dictaphone.recordTimerStart = new Date();
                Dictaphone.timerInterval = setInterval(e => {
                    Dictaphone.updateRecordTimer();
                }, 500);
            }

            Dictaphone.mediaRecorder.onstop = function (e) {
                const blob = new Blob(Dictaphone.chunks);
                Dictaphone.chunks = [];

                Dictaphone.audio.src = window.URL.createObjectURL(blob);

                Upload.prepare(new File([blob], "record.opus", { type: 'audio/ogg'}));
                Upload.name = 'record.opus';

                Dictaphone.recordTimeMs = new Date() - Dictaphone.recordTimerStart;
                Dictaphone.recordTimerStart = null;

                clearInterval(Dictaphone.timerInterval);

                Dictaphone.updateRecordTimer();
            }

            Dictaphone.audioUpload.onclick = function () {
                Upload.init();
                Dictaphone.clear();
            }

            Dictaphone.audio.onloadeddata = function () {
                Dictaphone.playPause.classList.add('enabled');
                Dictaphone.progressBar.value = 0;
                Dictaphone.updateRecordTimer();
            }

            Dictaphone.audio.ontimeupdate = function () {
                if (!mouseDownOnSlider) {
                    Dictaphone.progressBar.value = Dictaphone.audio.currentTime / Dictaphone.recordTimeMs * 100 * 1000;
                }

                Dictaphone.updateRecordTimer();
            }

            Dictaphone.audio.onplay = function () {
                Dictaphone.playPause.querySelector('i').innerHTML = 'pause';
            };

            Dictaphone.audio.onpause = function () {
                Dictaphone.playPause.querySelector('i').innerHTML = 'play_arrow';
            };

            Dictaphone.progressBar.onchange = function () {
                Dictaphone.audio.currentTime = Dictaphone.recordTimeMs / 100000 * Dictaphone.progressBar.value;
            }

            Dictaphone.playPause.onclick = function () {
                if (Dictaphone.audio.paused) {
                    Dictaphone.audio.play();
                } else {
                    Dictaphone.audio.pause();
                }
            };

            Dictaphone.progressBar.onmousedown = function() {
                mouseDownOnSlider = true;
            }

            Dictaphone.progressBar.onmouseup = function() {
                mouseDownOnSlider = false;
            }

            Dictaphone.mediaRecorder.start();
        }, error => console.log(error));
    },

    updateRecordTimer: function () {
        Dictaphone.timer.innerHTML = MovimUtils.cleanTime(Dictaphone.audio.currentTime);
        Dictaphone.timer.innerHTML += ' / ';
        Dictaphone.timer.innerHTML +=
            (Dictaphone.recordTimerStart != null)
                ? MovimUtils.cleanTime(Math.round((new Date() - Dictaphone.recordTimerStart) / 1000))
                : MovimUtils.cleanTime(Math.round(Dictaphone.recordTimeMs / 1000));
    },

    stop: function () {
        if (Dictaphone.mediaRecorder) {
            Dictaphone.mediaRecorder.stop();
        }

        Dictaphone.audioStream.getTracks().forEach(function (track) {
            track.stop();
        });
    }
}
