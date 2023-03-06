var Dictaphone = {
    mediaRecorder: null,
    audio: null,
    progressBar: null,
    playPause: null,
    audioUpload: null,
    timer: null,
    timerInterval: null,
    recordTimer: 0,
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
        Dictaphone.recordTimer = 0;
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

                Dictaphone.timerInterval = setInterval(e => {
                    Dictaphone.recordTimer++;
                    Dictaphone.updateRecordTimer();
                }, 1000);
            }

            Dictaphone.mediaRecorder.onstop = function (e) {
                const blob = new Blob(Dictaphone.chunks);
                Dictaphone.chunks = [];

                Dictaphone.audio.src = window.URL.createObjectURL(blob);

                Upload.prepare(new File([blob], "record.opus", { type: 'audio/ogg'}));
                Upload.name = 'record.opus';
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
                if (!mouseDownOnSlider && Dictaphone.audio.duration) {
                    Dictaphone.progressBar.value = Dictaphone.audio.currentTime / Dictaphone.audio.duration * 100;
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
                const pct = progressBar.value / 100;
                Dictaphone.audio.currentTime = (audio.duration || 0) * pct;
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
        Dictaphone.timer.innerHTML = (Dictaphone.audio && Number.isFinite(Dictaphone.audio.duration))
            ? MovimUtils.cleanTime(Dictaphone.audio.currentTime) + ' / ' + MovimUtils.cleanTime(Dictaphone.audio.duration)
            : Dictaphone.timer.innerHTML = MovimUtils.cleanTime(0) + ' / ' + MovimUtils.cleanTime(Dictaphone.recordTimer);
    },

    stop: function () {
        if (Dictaphone.mediaRecorder) {
            clearInterval(Dictaphone.timerInterval);
            Dictaphone.mediaRecorder.stop();
        }

        Dictaphone.audioStream.getTracks().forEach(function (track) {
            track.stop();
        });
    }
}
