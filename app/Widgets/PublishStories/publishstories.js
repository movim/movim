MAX_ZOOM = 5;
MIN_ZOOM = 0.1;
SCROLL_SENSITIVITY = 0.0005;

var PublishStories = {
    main: undefined,
    back: undefined,
    video: undefined,
    videoSelect: undefined,
    canvas: undefined,
    image: undefined,

    canvasMinWidth: 1080,
    canvasMinHeight: 1920,

    cameraOffset: { x: 0, y: 0 },
    cameraZoom: 1,
    isDragging: false,
    dragStart: { x: 0, y: 0 },
    initialPinchDistance: null,
    lastZoom: 1,

    init: function () {
        MovimTpl.pushAnchorState('story', function () {
            PublishStories.main.classList = '';
            PublishStories.close();
        });

        PublishStories.main = document.querySelector('#publishstories');
        PublishStories.back = document.querySelector("#publishstories #publishstoriesback");
        PublishStories.video = document.querySelector("#publishstories video");
        PublishStories.videoSelect = document.querySelector("#publishstories select#publishstoriessource");
        PublishStories.canvas = document.querySelector('#publishstories canvas');

        PublishStories.main.addEventListener('mousemove', PublishStories.onMainPointerMove);

        PublishStories.canvas.addEventListener('mousedown', PublishStories.onPointerDown);
        PublishStories.canvas.addEventListener('touchstart', (e) => PublishStories.handleTouch(e, PublishStories.onPointerDown));
        PublishStories.canvas.addEventListener('mouseup', PublishStories.onPointerUp);
        PublishStories.canvas.addEventListener('touchend', (e) => PublishStories.handleTouch(e, PublishStories.onPointerUp));
        PublishStories.canvas.addEventListener('mousemove', PublishStories.onPointerMove);
        PublishStories.canvas.addEventListener('touchmove', (e) => PublishStories.handleTouch(e, PublishStories.onPointerMove));
        PublishStories.canvas.addEventListener('wheel', (e) => PublishStories.adjustZoom(e.deltaY * SCROLL_SENSITIVITY));

        PublishStories.main.classList = 'show';
        PublishStories.back.onclick = () => { history.back(); };

        PublishStories.close();
        PublishStories.getStream().then(PublishStories.getDevices).then(PublishStories.gotDevices);

        MovimUtils.applyAutoheight();
    },

    draw: function () {
        if (PublishStories.image == undefined) return;

        PublishStories.canvas.width = PublishStories.canvasMinWidth;
        PublishStories.canvas.height = PublishStories.canvasMinHeight;

        let context = PublishStories.canvas.getContext('2d');
        ctx.clearRect(0, 0, PublishStories.image.width, PublishStories.image.height)
        context.fillStyle = MovimUtils.imageToHex(PublishStories.image);
        context.fillRect(0, 0, PublishStories.canvas.width, PublishStories.canvas.height);

        // Let's move!
        context.translate( PublishStories.canvas.width / 2, PublishStories.canvas.height / 2 );
        context.scale(PublishStories.cameraZoom, PublishStories.cameraZoom);
        context.translate(
            -PublishStories.canvas.width / 2 + PublishStories.cameraOffset.x,
            -PublishStories.canvas.height / 2 + PublishStories.cameraOffset.y
        );

        let adjustedHeight = PublishStories.canvas.height > PublishStories.image.height
            ? PublishStories.image.height
            : PublishStories.canvas.height;

        let ratio = adjustedHeight / PublishStories.image.height;

        context.drawImage(
            PublishStories.image,
            PublishStories.canvas.width / 2 - PublishStories.image.width * ratio / 2,
            PublishStories.canvas.height / 2 - adjustedHeight / 2,
            PublishStories.image.width * ratio,
            adjustedHeight
        );

        requestAnimationFrame(PublishStories.draw);
    },

    goToPublish: function () {
        let title = PublishStories.main.querySelector('form textarea[name=title]');

        if (title.value == '') {
            PublishStories_ajaxNoTitle();
            return;
        }

        PublishStories.main.classList.add('publish');
    },

    goToEdit: function () {
        PublishStories.main.classList.remove('publish');
    },

    publish: function () {
        PublishStories.main.querySelector('#publishactions').classList.add('uploading');

        PublishStories.canvas.toBlob(
            function (blob) {
                Upload.prepare(blob);
                Upload.name = 'story.jpg';
                Upload.init(true);
            },
            'image/jpeg',
            0.85
        );
    },

    close: function () {
        if (!PublishStories.video) return;

        let stream = PublishStories.video.srcObject;

        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }

        PublishStories.video.srcObject = null;
        PublishStories.image = undefined;
        PublishStories.main.querySelector('form').reset();
        PublishStories.main.querySelector('#publishactions').classList = '';

        PublishStories.reset();
        PublishStories.main.classList = '';
        PublishStories.main == undefined;
    },

    openImage: function () {
        PublishStories.main.querySelector('input[type=file]').click();
    },

    applyImage: function () {
        file = PublishStories.main.querySelector('input[type=file]').files[0];

        if (!file.type.match(/image.*/)) {
            console.log("Not a picture !");
            PublishStories.reset();
        } else {
            var reader = new FileReader();
            reader.readAsDataURL(file);
            reader.addEventListener('load', function (ev) {
                var image = new Image();
                image.src = reader.result;
                image.onload = function () {
                    PublishStories.image = image;
                    PublishStories.draw();
                    PublishStories.main.classList = 'edit';
                }
            });
        };
    },

    shoot: function () {
        if (PublishStories.imageCapture) {
            PublishStories.imageCapture.takePhoto()
                .then(blob => createImageBitmap(blob))
                .then(image => {
                    PublishStories.image = image;
                    PublishStories.draw();
                })
                .catch(error => console.log(error));

        } else {
            let ratio = PublishStories.canvasMinHeight / PublishStories.video.videoHeight;
            if (ratio < 1) ratio = 1;

            var canvas = document.createElement('canvas');
            canvas.width = PublishStories.video.videoWidth * ratio;
            canvas.height = PublishStories.video.videoHeight * ratio;

            var context = canvas.getContext('2d');
            context.drawImage(PublishStories.video, 0, 0, canvas.width, canvas.height);

            var image = new Image();
            image.src = canvas.toDataURL();

            PublishStories.video.pause();

            image.onload = function () {
                PublishStories.image = image;
                PublishStories.draw();
            };
        }

        PublishStories.main.classList = 'edit';
    },

    reset: function () {
        PublishStories.main.classList = 'shoot';
        PublishStories.video.play();

        PublishStories.cameraOffset = { x: 0, y: 0 };
        PublishStories.cameraZoom = PublishStories.lastZoom = 1;
    },

    getStream: function () {
        PublishStories.main.classList = 'wait';

        if (PublishStories.video.srcObject) {
            PublishStories.video.srcObject.getTracks().forEach(track => track.stop());
        }

        const videoSource = PublishStories.videoSelect.value;
        const constraints = {
            video: {
                deviceId: videoSource ? { exact: videoSource } : undefined,
                width: { ideal: 4096 },
                height: { ideal: 4096 }
            }
        };

        return navigator.mediaDevices.getUserMedia(constraints)
            .then(PublishStories.gotStream);
    },

    gotStream: function (stream) {
        PublishStories.main.classList = 'shoot';

        PublishStories.videoSelect.selectedIndex = [...PublishStories.videoSelect.options].
            findIndex(option => option.text === stream.getVideoTracks()[0].label);
        PublishStories.video.srcObject = stream;
        PublishStories.video.play();

        // We try to use ImageCapture
        if (typeof (ImageCapture) != 'undefined') {
            const track = stream.getVideoTracks()[0];
            PublishStories.imageCapture = new ImageCapture(track);
        }

        // If we cancel after the authorization was given
        if (PublishStories.main.classList == '') {
            PublishStories.close();
        };
    },

    getDevices: function () {
        return navigator.mediaDevices.enumerateDevices();
    },

    gotDevices: function (devicesInfo) {
        PublishStories.videoSelect.innerHTML = '';

        for (const deviceInfo of devicesInfo) {
            if (deviceInfo.kind === 'videoinput') {
                const option = document.createElement('option');
                option.value = deviceInfo.deviceId;
                option.text = deviceInfo.label || `Camera ${videoSelect.length + 1}`;

                if (!PublishStories.videoSelect.querySelector('option[value="' + deviceInfo.deviceId + '"]')) {
                    PublishStories.videoSelect.appendChild(option);
                }
            }
        }

        if (PublishStories.videoSelect.options.length >= 2) {
            document.querySelector("#PublishStories #PublishStoriesswitch").classList.add('enabled');
        }
    },

    onPointerDown: function (e) {
        PublishStories.isDragging = true
        PublishStories.dragStart.x = MovimUtils.getEventLocation(e).x / PublishStories.cameraZoom - PublishStories.cameraOffset.x
        PublishStories.dragStart.y = MovimUtils.getEventLocation(e).y / PublishStories.cameraZoom - PublishStories.cameraOffset.y
    },

    onPointerUp: function (e) {
        PublishStories.isDragging = false
        PublishStories.initialPinchDistance = null
        PublishStories.lastZoom = PublishStories.cameraZoom
    },

    onMainPointerMove: function (e) {
        if (!PublishStories.main.classList.contains('edit') || !MovimUtils.getEventLocation(e)) return;

        let borders = PublishStories.canvas.getBoundingClientRect();

        if (MovimUtils.getEventLocation(e).x < borders.left
         || MovimUtils.getEventLocation(e).x > borders.right
         || MovimUtils.getEventLocation(e).y < borders.top
         || MovimUtils.getEventLocation(e).y > borders.bottom) {
            PublishStories.onPointerUp(e);
        }
    },

    onPointerMove: function (e) {
        if (PublishStories.isDragging) {
            PublishStories.cameraOffset.x = MovimUtils.getEventLocation(e).x / PublishStories.cameraZoom - PublishStories.dragStart.x
            PublishStories.cameraOffset.y = MovimUtils.getEventLocation(e).y / PublishStories.cameraZoom - PublishStories.dragStart.y
        }
    },

    handleTouch: function (e, singleTouchHandler) {
        if (e.touches.length == 1) {
            singleTouchHandler(e)
        }
        else if (e.type == "touchmove" && e.touches.length == 2) {
            PublishStories.isDragging = false
            PublishStories.handlePinch(e)
        }
    },

    handlePinch: function (e) {
        e.preventDefault()

        let touch1 = { x: e.touches[0].clientX, y: e.touches[0].clientY }
        let touch2 = { x: e.touches[1].clientX, y: e.touches[1].clientY }

        // This is distance squared, but no need for an expensive sqrt as it's only used in ratio
        let currentDistance = (touch1.x - touch2.x) ** 2 + (touch1.y - touch2.y) ** 2

        if (PublishStories.initialPinchDistance == null) {
            PublishStories.initialPinchDistance = currentDistance
        }
        else {
            PublishStories.adjustZoom(null, currentDistance / PublishStories.initialPinchDistance)
        }
    },

    adjustZoom: function (zoomAmount, zoomFactor) {
        if (!PublishStories.isDragging) {
            if (zoomAmount) {
                PublishStories.cameraZoom += zoomAmount
            }
            else if (zoomFactor) {
                PublishStories.cameraZoom = zoomFactor * PublishStories.lastZoom
            }

            PublishStories.cameraZoom = Math.min(PublishStories.cameraZoom, MAX_ZOOM)
            PublishStories.cameraZoom = Math.max(PublishStories.cameraZoom, MIN_ZOOM)
        }
    }
}

Upload.attach((file) => {
    if (PublishStories.main && PublishStories.main.classList.contains('publish')) {
        PublishStories.main.querySelector('#publishactions').classList.remove('uploading');
        PublishStories.main.querySelector('#publishactions').classList.add('publishing');
        PublishStories_ajaxPublish(MovimUtils.formToJson('metadata'), file.id);
    }
});

Upload.fail(() => {
    PublishStories.main.querySelector('#publishactions').classList.remove('uploading');
});

Upload.progress((percent) => {
    if (PublishStories.main && PublishStories.main.classList.contains('publish')) {
        PublishStories.main.querySelector('#publishactionsprogress').innerHTML =
            percent + '%';
    }
});