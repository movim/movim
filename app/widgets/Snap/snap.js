var Snap = {
    snap: undefined,
    video: undefined,
    videoSelect: undefined,
    canvas: undefined,
    wait: undefined,

    gotStream: function() {
        const constraints = {
            video: {
                deviceId: Snap.videoSelect.value,
                width: { ideal: 4096 },
                height: { ideal: 4096 }
            }
        };

        if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia(constraints)
                .then(stream => {
                    Snap.video.srcObject = stream;
                    Snap.video.play();

                    // If we cancel after the authorization was given
                    if (Snap.snap.classList == '') {
                        Snap.close();
                    };
                });
        }
    },
    gotDevices: function(deviceInfos) {
        Snap.videoSelect.innerText = '';

        const ids = [];

        for (let i = 0; i !== deviceInfos.length; ++i) {
            const deviceInfo = deviceInfos[i];

            if (deviceInfo.kind === 'videoinput' && !ids.includes(deviceInfo.deviceId)) {
                const option = document.createElement('option');
                option.value = deviceInfo.deviceId;
                option.text = deviceInfo.label;
                Snap.videoSelect.add(option);
                ids.push(deviceInfo.deviceId);
            }
        }

        Snap.videoSelect.addEventListener('change', e => Snap.gotStream() );
        Snap.gotStream();

        if (ids.length >= 2) {
            document.querySelector("#snap #snapswitch").classList.add('enabled');
        }

        snap.classList = 'shoot';
    },
    shoot: function() {
        Snap.canvas.width = Snap.video.videoWidth;
        Snap.canvas.height = Snap.video.videoHeight;
        var context = Snap.canvas.getContext('2d');
        context.drawImage(Snap.video, 0, 0, Snap.video.videoWidth, Snap.video.videoHeight);
        Snap.video.pause();

        Upload.name = 'snapshot.jpg';

        Snap.canvas.toBlob(
            function (blob) {
                Upload.prepare(blob);
            },
            'image/jpeg',
            0.85
        );

        Snap.snap.classList = 'upload';
    },
    clear: function() {
        Snap.video.play();
        var context = Snap.canvas.getContext('2d');
        context.clearRect(0, 0, Snap.canvas.width, Snap.canvas.height);
    },
    close: function() {
        let stream = Snap.video.srcObject;

        if (stream) {
            stream.getTracks().forEach(function(track) {
                track.stop();
            });
        }

        Snap.video.srcObject = null;
    },
    end: function() {
        Snap.snap.classList = '';
        Snap.wait.style.backgroundImage = '';
        Snap.close();
    },
    init : function() {
        Snap.snap = document.querySelector('#snap');
        Snap.video = document.querySelector('#snap video');
        Snap.videoSelect = document.querySelector('#snap select#snapsource');
        Snap.canvas = document.querySelector('#snap canvas');
        Snap.wait = document.querySelector("#snap #snapwait");

        Snap.close(); // Just in case

        Snap.snap.classList = 'wait';

        navigator.mediaDevices.enumerateDevices().then(devices => Snap.gotDevices(devices));

        Snap.video.play();

        document.querySelector("#snap #snapshoot").onclick = () => {
            Snap.shoot();
        };

        document.querySelector("#snap #snapupload").onclick = () => {
            Snap.snap.classList = 'wait';
            Upload.init();
        };

        document.querySelector("#snap #snapback").onclick = () => {
            Snap.snap.classList = '';
            Snap.close();
        };

        document.querySelector("#snap #snapclose").onclick = () => {
            Snap.snap.classList = 'shoot';
            Snap.video.play();
            Upload.abort();
        };

        document.querySelector("#snap #snapswitch").onclick = () => {
            Snap.videoSelect.selectedIndex++;

            // No empty selection
            if (Snap.videoSelect.selectedIndex == -1) {
                Snap.videoSelect.selectedIndex++;
            }

            Snap.close();
            Snap.gotStream();
        };
    }
}

Upload.attach((file) => {
    const page = MovimUtils.urlParts().page;

    if (page != 'chat') {
        document.querySelector('input[name=embed]').value = file.uri;
        PublishBrief.checkEmbed();
    }

    Snap.end();
});

Upload.progress((percent) => Snap.wait.style.backgroundImage
    = 'linear-gradient(to top, rgba(0, 0, 0, 0.5) ' + percent + '%, transparent ' + percent + '%)');
Upload.fail(() => { Snap.snap.classList = 'upload'; Snap.wait.style.backgroundImage = ''; });