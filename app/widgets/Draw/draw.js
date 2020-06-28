var Draw = {
    SMALL: 4,
    MEDIUM: 6,
    BIG: 8,

    CROP_PADDING: 0.15,

    draw: null,  // widget wrapper

    canvas: null,  // drawable
    ctx: null,
    bg: null,  // non drawable, only used for background

    drawing: false,
    mousePos: null,
    lastPos: null,

    drawingData: null,  // data structure for saving drawn points

    control: null,
    actions: null,
    topNav: null,
    save: null,  // button

    backgroundCanvas: null,  // background coming from another widget as a canvas
    bgHeight: null,  // natural height of background
    bgWidth: null,  // natural width of background
    ratio: 1,  // upscale ratio

    init: function (backgroundCanvas) {
        Draw.drawingData = [];
        Draw.backgroundCanvas = backgroundCanvas;
        Draw.topNav = document.querySelector('.draw-top-nav');
        Draw.control = document.querySelector('.draw-control');
        Draw.actions = document.querySelector('.draw-actions');

        Draw.draw = document.getElementById('draw');
        const canvasWrapper = document.querySelector('#draw .canvas');
        const colors = document.querySelectorAll('.draw-colors li');
        const widths = document.querySelectorAll('.draw-widths li');
        const eraser = document.querySelector('.draw-eraser');

        Draw.draw.classList.add('open');

        let height, width;
        if (Draw.backgroundCanvas) {
            Draw.bgHeight = Draw.backgroundCanvas.height;
            Draw.bgWidth = Draw.backgroundCanvas.width;
            const dheight = document.body.clientHeight;
            const dwidth = document.body.clientWidth;
            if (Draw.bgHeight <= dheight && Draw.bgWidth <= dwidth) {
                height = Draw.bgHeight;
                width = Draw.bgWidth;
            } else {
                const bgTaller = Draw.bgHeight / Draw.bgWidth > dheight / dwidth;
                if (Draw.bgHeight <= dheight || !bgTaller) {
                    width = dwidth;
                    height = dwidth * Draw.bgHeight / Draw.bgWidth;
                    Draw.ratio = Draw.bgWidth / dwidth;
                } else if (Draw.bgWidth <= dwidth || bgTaller) {
                    height = dheight;
                    width = dheight * Draw.bgWidth / Draw.bgHeight;
                    Draw.ratio = Draw.bgHeight / dheight;
                }
            }
        } else {
            height = document.body.clientHeight;
            width = document.body.clientWidth;
        }

        canvasWrapper.style.height = `${height}px`;
        canvasWrapper.style.width = `${width}px`;

        Draw.canvas = document.getElementById('draw-canvas');
        Draw.canvas.width = width;
        Draw.canvas.height = height;
        Draw.ctx = Draw.canvas.getContext('2d');
        Draw.ctx.lineCap = 'round';

        Draw.screen = document.getElementById('screen-canvas');
        Draw.screen.width = width;
        Draw.screen.height = height;
        Draw.sctx = Draw.screen.getContext('2d');
        Draw.sctx.lineCap = Draw.ctx.lineCap;

        Draw.bg = document.getElementById('draw-background');
        Draw.bg.width = width;
        Draw.bg.height = height;
        bgctx = Draw.bg.getContext('2d');

        if (Draw.backgroundCanvas) {
            // copy over background image
            bgctx.drawImage(Draw.backgroundCanvas, 0, 0, width, height);
        } else {
            // fill canvas with white
            bgctx.fillStyle = 'white';
            bgctx.fillRect(0, 0, width, height);
        }

        // init controls
        Draw.ctx.strokeStyle = '#000';
        Draw.sctx.strokeStyle = '#000';
        colors.forEach(item => item.classList.remove('selected'));
        eraser.classList.remove('selected');
        document.querySelector('[data-color=black]').classList.add('selected');
        Draw.ctx.lineWidth = Draw.MEDIUM;
        Draw.sctx.lineWidth = Draw.MEDIUM;
        widths.forEach(item => item.classList.remove('selected'));
        document.querySelector('[data-width=medium]').classList.add('selected');


        if (Draw.draw.classList.contains('bound')) return;

        // Get a regular interval for drawing to the screen
        window.requestAnimFrame = (function (callback) {
            return window.requestAnimationFrame ||
                window.webkitRequestAnimationFrame ||
                window.mozRequestAnimationFrame ||
                window.oRequestAnimationFrame ||
                window.msRequestAnimaitonFrame ||
                function (callback) {
                    window.setTimeout(callback, 1000 / 60);
                };
        })();
        // Allow for animation
        (function drawLoop() {
            requestAnimFrame(drawLoop);
            Draw.renderCanvas();
        })();

        Draw.screen.addEventListener('mousedown', Draw.startDrawing, true);
        Draw.screen.addEventListener('mouseenter', Draw.startDrawing, false);
        Draw.screen.addEventListener('mouseup', Draw.stopDrawing, false);
        Draw.screen.addEventListener('mouseleave', Draw.stopDrawing, false);
        Draw.screen.addEventListener('mousemove', function (e) {
            Draw.mousePos = Draw.getPos(Draw.screen, e);
        }, false);

        // Set up touch events for mobile, etc
        Draw.screen.addEventListener('touchstart', function (e) {
            Draw.mousePos = Draw.getPos(Draw.screen, e);
            var touch = e.touches[0];
            var mouseEvent = new MouseEvent('mousedown', {
                clientX: touch.clientX,
                clientY: touch.clientY,
                buttons: 1
            });
            Draw.screen.dispatchEvent(mouseEvent);
        }, false);
        Draw.screen.addEventListener('touchend', Draw.stopDrawing, false);
        Draw.screen.addEventListener('touchmove', function (e) {
            var touch = e.touches[0];
            var mouseEvent = new MouseEvent('mousemove', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            Draw.screen.dispatchEvent(mouseEvent);
        }, false);

        document.body.addEventListener('touchstart', Draw.disableForCanvas, false);
        document.body.addEventListener('touchend', Draw.disableForCanvas, false);
        document.body.addEventListener('touchmove', Draw.disableForCanvas, false);

        // Clear canvas
        const clear = document.getElementById('draw-clear');
        clear.addEventListener('click', (e) => {
            const rect = Draw.screen.getBoundingClientRect();
            Draw.ctx.clearRect(0, 0, rect.width, rect.height);
            Draw.drawingData = [];
        }, false);

        // Save (background +) drawing
        Draw.save = document.querySelector('#draw-save');
        Draw.save.onclick = (e) => {
            const finalCanvas = document.createElement('canvas');
            let crop, height, width, bg;

            if (Draw.backgroundCanvas) {
                width = Draw.bgWidth;
                height = Draw.bgHeight;
                bg = Draw.backgroundCanvas;
            } else {
                crop = Draw.autoCropDrawing();
                width = crop.width;
                height = crop.height;
                bg = Draw.bg;
            }
            finalCanvas.setAttribute('width', width);
            finalCanvas.setAttribute('height', height);

            const finalctx = finalCanvas.getContext('2d');
            finalctx.lineCap = 'round';

            if (Draw.backgroundCanvas) {
                Draw.upscaleDrawing(finalctx);
            } else {
                finalctx.putImageData(crop, 0, 0);
            }

            // add background
            finalctx.globalCompositeOperation = 'destination-over';
            finalctx.drawImage(
                bg,
                0, 0,
                width,
                height
            );

            // Disable button
            Draw.save.classList.add('disabled');

            finalCanvas.toBlob(
                function (blob) {
                    Upload.prepare(blob);
                    Upload.name = 'drawing.jpg';
                    Upload.init();
                },
                'image/jpeg',
                0.85
            );
        };

        // Use the eraser
        eraser.addEventListener('click', function(e) {
            colors.forEach(item => item.classList.remove('selected'));
            this.classList.add('selected');

            Draw.ctx.globalCompositeOperation = 'destination-out';
        }, false);

        // Change pencil color
        for (let i = 0; i < colors.length; i++) {
            colors[i].addEventListener('click', function(e) {
                colors.forEach(item => item.classList.remove('selected'));
                eraser.classList.remove('selected');
                this.classList.add('selected');

                Draw.ctx.globalCompositeOperation = 'source-over';
                Draw.ctx.strokeStyle = window.getComputedStyle(colors[i].querySelector('span.primary')).backgroundColor;
                Draw.sctx.strokeStyle = window.getComputedStyle(colors[i].querySelector('span.primary')).backgroundColor;
            });
        }

        // Change pencil thickness
        for (let i = 0; i < widths.length; i++) {
            widths[i].addEventListener('click', function(e) {
                widths.forEach(item => item.classList.remove('selected'));
                this.classList.add('selected');

                let width;
                switch (this.dataset.width) {
                    case 'small':
                        width = Draw.SMALL;
                        break;
                    case 'medium':
                        width = Draw.MEDIUM;
                        break;
                    case 'big':
                        width = Draw.BIG;
                        break;
                    default:
                        width = Draw.SMALL;
                }
                Draw.ctx.lineWidth = width;
                Draw.sctx.lineWidth = width;
            });
        }

        const drawback = document.querySelector('#draw #drawback');
        drawback.addEventListener('click', () => {
            Draw.draw.classList.remove('open');
        });

        // Add a flag to not re-bind event listeners
        Draw.draw.classList.add('bound');
    },

    stopDrawing: function(e) {
        // print to actual canvas
        if (Draw.drawingData.length)
            Draw.smoothenLine(Draw.drawingData[Draw.drawingData.length -1], Draw.ctx, 1)
        // cleanup screen
        const rect = Draw.screen.getBoundingClientRect();
        Draw.sctx.clearRect(0, 0, rect.width, rect.height);

        Draw.drawing = false;
        Draw.lastPos = null;
        Draw.mousePos = null;

        // show coontrols
        Draw.topNav.classList.remove('drawing');
        Draw.actions.classList.remove('drawing');
        Draw.control.classList.remove('drawing');

        Draw.ctx.beginPath();
        Draw.sctx.beginPath();
    },

    startDrawing: function(e) {
        if (e.buttons == 1) {
            Draw.drawing = true;

            // hide controls
            Draw.topNav.classList.add('drawing');
            Draw.actions.classList.add('drawing');
            Draw.control.classList.add('drawing');

            // save drawing data
            const data = {
                gco: Draw.sctx.globalCompositeOperation,
                width: Draw.sctx.lineWidth,
                color: Draw.sctx.strokeStyle,
                points: []
            }
            Draw.drawingData.push(data);

            Draw.lastPos = Draw.getPos(Draw.screen, e);
        }
    },

    upscaleDrawing: function (finalctx) {
        // re-draw upscaled
        for (let i = 0; i < Draw.drawingData.length; i++) {
            finalctx.globalCompositeOperation = Draw.drawingData[i].gco;
            finalctx.lineWidth = Draw.drawingData[i].width * Draw.ratio;
            finalctx.strokeStyle = Draw.drawingData[i].color;

            Draw.smoothenLine(Draw.drawingData[i], finalctx, Draw.ratio);
            finalctx.beginPath();
        }
    },

    smoothenLine(line, ctx, ratio) {
        let j = 0;
        // keep only half of the points
        const sample = line.points.filter((point, index) => index % 2 == 0)

        while (sample.length < 4) {
            sample.push(sample[sample.length - 1]);
        }

        if (sample.length >= 4) {
            // move to 1st point of the line
            ctx.moveTo(
                sample[j].x * ratio,
                sample[j].y * ratio
            );
            for (j = 1; j < sample.length - 2; j++) {
                // define controle point as the mean point between current point and next point
                const c = (sample[j].x + sample[j + 1].x) / 2;
                const d = (sample[j].y + sample[j + 1].y) / 2;

                // draw a curve between
                ctx.quadraticCurveTo(
                    sample[j].x * ratio,
                    sample[j].y * ratio,
                    c * ratio,
                    d * ratio
                );
            }
            ctx.quadraticCurveTo(
                sample[j].x * ratio,
                sample[j].y * ratio,
                sample[j + 1].x * ratio,
                sample[j + 1].y * ratio
            );
        }
        ctx.stroke();
    },

    autoCropDrawing: function () {
        const rect = Draw.screen.getBoundingClientRect();
        let height = rect.height;
        let width = rect.width;

        const imgData = Draw.ctx.getImageData(0, 0, width, height);
        let maxx = 0, minx = width, maxy = 0, miny = height,
            index;

        // list all non transparent pixels
        for (let y = 0; y < height; y++) {
            for (let x = 0; x < width; x++) {
                index = (y * width + x) * 4;
                if (imgData.data[index + 3] > 0) {
                    minx = Math.min(x, minx);
                    miny = Math.min(y, miny);
                    maxx = Math.max(x, maxx);
                    maxy = Math.max(y, maxy);
                }
            }
        }

        width = maxx - minx + 1;
        height = maxy - miny + 1;

        return Draw.ctx.getImageData(
            minx - (width * Draw.CROP_PADDING),
            miny - (height * Draw.CROP_PADDING),
            width * (1 + 2 * Draw.CROP_PADDING),
            height * (1 + 2 * Draw.CROP_PADDING)
        )
    },

    // Get the position of the mouse/touch relative to the canvas
    getPos: function(canvasDom, event) {
        var rect = canvasDom.getBoundingClientRect();
        let x, y;
        if (event.touches) {
            x = event.touches[0].clientX - rect.left;
            y = event.touches[0].clientY - rect.top;
        } else {
            x = event.clientX - rect.left;
            y = event.clientY - rect.top;
        }

        if (Draw.drawing) {
            let points = Draw.drawingData[Draw.drawingData.length - 1].points;
            points.push({ x, y });
        }

        return { x, y };
    },

    // Draw to the canvas
    renderCanvas: function() {
        if (Draw.drawing && Draw.lastPos && Draw.mousePos) {
            Draw.sctx.moveTo(Draw.lastPos.x, Draw.lastPos.y);
            Draw.sctx.lineTo(Draw.mousePos.x, Draw.mousePos.y);
            Draw.sctx.stroke();
            Draw.lastPos = Draw.mousePos;
        }
    },

    disableForCanvas: function(e) {
        if (e.target.tagName == 'canvas') {
            e.preventDefault();
        }
    }
};

Upload.attach((file) => {
    if (Draw.draw) Draw.draw.classList = '';
    if (Draw.save) {
        Draw.save.classList.remove('disabled');
        Draw.save.style.backgroundImage = '';
    }
});

Upload.fail(() => {
    if (Draw.draw) Draw.draw.classList = 'upload';
    if (Draw.save) {
        Draw.save.classList.remove('disabled');
        Draw.save.style.backgroundImage = '';
    }
});

Upload.progress((percent) => {
    if (Draw.save) {
        Draw.save.style.backgroundImage
            = 'linear-gradient(to top, rgba(0, 0, 0, 0.5) ' + percent + '%, transparent ' + percent + '%)';
    }
});

movimAddOnload(() => Draw_ajaxHttpGet());
