var Snap = {
    snap: undefined,
    video: undefined,
    videoSelect: undefined,
    canvas: undefined,
    wait: undefined,
    imageCapture: null,

    init: function () {
        MovimTpl.pushAnchorState('snap', function () {
            Snap.snap.classList = '';
            Snap.close();
        });

        Snap.snap = document.querySelector('#snap');
        Snap.canvas = document.querySelector('#snap canvas');
        Snap.wait = document.querySelector("#snap #snapwait");

        Snap.video = document.querySelector('#snap video');
        Snap.videoSelect = document.querySelector('#snap select#snapsource');

        Snap.close();

        Snap.getStream().then(Snap.getDevices).then(Snap.gotDevices);

        document.querySelector("#snap #snapshoot").onclick = () => Snap.shoot();

        document.querySelector("#snap #snapswitch").onclick = () => {
            Snap.snap.classList = 'init';

            Snap.videoSelect.selectedIndex++;

            // No empty selection
            if (Snap.videoSelect.selectedIndex == -1) {
                Snap.videoSelect.selectedIndex++;
            }

            Toast.send(Snap.videoSelect.options[Snap.videoSelect.selectedIndex].label);
            Snap.getStream();
        };

        Snap.snap.classList = 'init';

        document.querySelector("#snap #snapupload").onclick = () => {
            Snap.snap.classList = 'wait';
            Upload.init();
        };

        document.querySelector("#snap #snapdraw").onclick = () => {
            Snap.snap.classList = '';
            Snap.close();
            Draw.initCanvas = Snap.canvas;
            Draw_ajaxHttpGet();
        };

        document.querySelector("#snap #snapback").onclick = () => {
            history.back();
        };

        document.querySelector("#snap #snapclose").onclick = () => {
            Snap.snap.classList = 'shoot';
            Snap.video.play();
            Upload.abort();
        };
    },

    close: function () {
        if (!Snap.video) return;

        let stream = Snap.video.srcObject;

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }

        Snap.video.srcObject = null;
    },

    getStream: function () {
        Snap.snap.classList = 'wait';

        if (Snap.video.srcObject) {
            Snap.video.srcObject.getTracks().forEach(track => track.stop());
        }

        const videoSource = Snap.videoSelect.value;
        const constraints = {
            video: {
                deviceId: videoSource ? { exact: videoSource } : undefined,
                width: { ideal: 1920 },
                height: { ideal: 1920 }
            }
        };

        return navigator.mediaDevices.getUserMedia(constraints)
            .then(Snap.gotStream);
    },

    gotStream: function (stream) {
        Snap.snap.classList = 'shoot';

        Snap.videoSelect.selectedIndex = [...Snap.videoSelect.options].
            findIndex(option => option.text === stream.getVideoTracks()[0].label);
        Snap.video.srcObject = stream;

        // We try to use ImageCapture
        if (typeof (ImageCapture) != 'undefined') {
            const track = stream.getVideoTracks()[0];
            Snap.imageCapture = new ImageCapture(track);
        }

        // If we cancel after the authorization was given
        if (Snap.snap.classList == '') {
            Snap.close();
        };
    },

    getDevices: function () {
        return navigator.mediaDevices.enumerateDevices();
    },

    gotDevices: function (devicesInfo) {
        Snap.videoSelect.innerHTML = '';

        for (const deviceInfo of devicesInfo) {
            if (deviceInfo.kind === 'videoinput') {
                const option = document.createElement('option');
                option.value = deviceInfo.deviceId;
                option.text = deviceInfo.label || `Camera ${videoSelect.length + 1}`;

                if (!Snap.videoSelect.querySelector('option[value="' + deviceInfo.deviceId + '"]')) {
                    Snap.videoSelect.appendChild(option);
                }
            }
        }

        if (Snap.videoSelect.options.length >= 2) {
            document.querySelector("#snap #snapswitch").classList.add('enabled');
        }
    },

    shoot: function () {
        if (Snap.imageCapture) {
            Snap.imageCapture.takePhoto()
                .then(blob => createImageBitmap(blob))
                .then(image => {
                    Snap.canvas.width = image.width;
                    Snap.canvas.height = image.height;
                    var context = Snap.canvas.getContext('2d');
                    context.drawImage(image, 0, 0, image.width, image.height);

                    Snap.compress();
                })
                .catch(error => console.log(error));

        } else {
            Snap.canvas.width = Snap.video.videoWidth;
            Snap.canvas.height = Snap.video.videoHeight;
            var context = Snap.canvas.getContext('2d');
            context.drawImage(Snap.video, 0, 0, Snap.video.videoWidth, Snap.video.videoHeight);
            Snap.video.pause();

            Snap.compress();
        }
    },
    compress: function () {
        Snap.canvas.toBlob(
            function (blob) {
                Upload.prepare(blob);
            },
            'image/jpeg',
            0.85
        );

        Upload.name = 'snapshot.jpg';
        Snap.snap.classList = 'upload';
    },
    clear: function () {
        Snap.video.play();
        var context = Snap.canvas.getContext('2d');
        context.clearRect(0, 0, Snap.canvas.width, Snap.canvas.height);
    },
    end: function () {
        Snap.snap.classList = '';
        Snap.wait.style.backgroundImage = '';
        Snap.close();
    }
}

MovimEvents.registerWindow('loaded', 'snap', () => {
    Upload.attach((file) => {
        if (Snap.snap) Snap.end();
    });

    Upload.progress((percent) => {
        if (Snap.wait) {
            Snap.wait.style.backgroundImage = 'linear-gradient(to top, rgba(0, 0, 0, 0.5) ' + percent + '%, transparent ' + percent + '%)';
        }
    });

    Upload.fail(() => {
        if (Snap.snap) {
            Snap.snap.classList = 'upload'; Snap.wait.style.backgroundImage = '';
        }
    });
});