var Draw = {
    SMALL: 4,
    MEDIUM: 6,
    BIG: 8,

    canvas: null,
    canvasbg: null,
    ctx: null,
    draw: null,
    save: null,

    drawing: false,
    mousePos: { x: 0, y: 0 },
    lastPos: this.mousePos,

    init: function (snapBackground) {
        Draw.draw = document.getElementById('draw');
        const canvasWrapper = document.querySelector('#draw .canvas');
        const colors = document.querySelectorAll('.draw-colors li');
        const widths = document.querySelectorAll('.draw-widths li');
        const eraser = document.querySelector('.draw-eraser');

        Draw.draw.classList.add('open');

        let height, width;
        if (snapBackground) {
            const sheight = Snap.canvas.height;
            const swidth = Snap.canvas.width;
            const dheight = document.body.clientHeight;
            const dwidth = document.body.clientWidth;
            if (sheight <= dheight && swidth <= dwidth) {
                height = sheight;
                width = swidth;
            } else {
                const s_taller = sheight / swidth > dheight / dwidth;
                if (sheight <= dheight || !s_taller) {
                    width = dwidth;
                    height = dwidth * sheight / swidth;
                } else if (swidth <= dwidth || s_taller) {
                    height = dheight;
                    width = dheight * swidth / sheight;
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

        Draw.canvasbg = document.getElementById('draw-background');
        Draw.canvasbg.width = width;
        Draw.canvasbg.height = height;
        bgctx = Draw.canvasbg.getContext('2d');

        if (snapBackground) {
            // copy over snap image
            bgctx.drawImage(Snap.canvas, 0, 0, width, height);
        } else {
            // fill canvas with white
            bgctx.fillStyle = 'white';
            bgctx.fillRect(0, 0, width, height);
        }

        // init controls
        Draw.ctx.strokeStyle = Draw.BLACK;
        colors.forEach(item => item.classList.remove('selected'));
        eraser.classList.remove('selected');
        document.querySelector('[data-color=black]').classList.add('selected');
        Draw.ctx.lineWidth = Draw.MEDIUM;
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

        Draw.canvas.addEventListener('mousedown', Draw.startDrawing, true);
        Draw.canvas.addEventListener('mouseenter', Draw.startDrawing, false);
        Draw.canvas.addEventListener('mouseup', Draw.stopDrawing, false);
        Draw.canvas.addEventListener('mouseleave', Draw.stopDrawing, false);
        Draw.canvas.addEventListener('mousemove', function (e) {
            Draw.mousePos = Draw.getPos(Draw.canvas, e);
        }, false);

        // Set up touch events for mobile, etc
        Draw.canvas.addEventListener('touchstart', function (e) {
            Draw.mousePos = Draw.getPos(Draw.canvas, e);
            var touch = e.touches[0];
            var mouseEvent = new MouseEvent('mousedown', {
                clientX: touch.clientX,
                clientY: touch.clientY,
                buttons: 1
            });
            Draw.canvas.dispatchEvent(mouseEvent);
        }, false);
        Draw.canvas.addEventListener('touchend', Draw.stopDrawing, false);
        Draw.canvas.addEventListener('touchmove', function (e) {
            var touch = e.touches[0];
            var mouseEvent = new MouseEvent('mousemove', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            Draw.canvas.dispatchEvent(mouseEvent);
        }, false);

        document.body.addEventListener('touchstart', Draw.disableForCanvas, false);
        document.body.addEventListener('touchend', Draw.disableForCanvas, false);
        document.body.addEventListener('touchmove', Draw.disableForCanvas, false);

        // Clear canvas
        const clear = document.getElementById('draw-clear');
        clear.addEventListener('click', (e) => {
            const rect = Draw.canvas.getBoundingClientRect();
            Draw.ctx.clearRect(0, 0, rect.width, rect.height);
        }, false);

        // Save (background +) drawing
        Draw.save = document.getElementById('draw-save');
        Draw.save.onclick = (e) => {
            const rect = Draw.canvas.getBoundingClientRect();
            const finalCanvas = document.createElement('canvas');

            finalCanvas.setAttribute('width', rect.width);
            finalCanvas.setAttribute('height', rect.height);

            const bgimg = document.getElementById('draw-background');
            const finalctx = finalCanvas.getContext('2d');

            finalctx.drawImage(bgimg, 0, 0, rect.width, rect.height);
            finalctx.drawImage(Draw.canvas, 0, 0, rect.width, rect.height);

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
            Draw.ctx.strokeStyle = 'rgba(0,0,0,1)';
        }, false);

        // Change pencil color
        for (let i = 0; i < colors.length; i++) {
            colors[i].addEventListener('click', function(e) {
                colors.forEach(item => item.classList.remove('selected'));
                eraser.classList.remove('selected');
                this.classList.add('selected');

                Draw.ctx.globalCompositeOperation = 'source-over';
                Draw.ctx.strokeStyle = window.getComputedStyle(colors[i].querySelector('span.primary')).backgroundColor;
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
            });
        }

        const drawback = document.querySelector('#draw #drawback');
        drawback.addEventListener('click', () => {
            Draw.draw.classList.remove('open');
        });

        // Add a fleg to not re-bind event listeners
        Draw.draw.classList.add('bound');

    },

    stopDrawing: function(e) {
        Draw.drawing = false;
        Draw.ctx.beginPath();
    },

    startDrawing: function(e) {
        if (e.buttons == 1) {
            Draw.drawing = true;
            Draw.lastPos = Draw.getPos(Draw.canvas, e);
        }
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
        return { x, y };
    },

    // Draw to the canvas
    renderCanvas: function() {
        if (Draw.drawing) {
            Draw.ctx.moveTo(Draw.lastPos.x, Draw.lastPos.y);
            Draw.ctx.lineTo(Draw.mousePos.x, Draw.mousePos.y);
            Draw.ctx.stroke();
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
    if (Draw.save) Draw.save.querySelector('span.primary').style.backgroundImage = '';
});

Upload.progress((percent) => {
    if (Draw.save) {
        Draw.save.querySelector('span.primary').style.backgroundImage
            = 'linear-gradient(to top, rgba(0, 0, 0, 0.5) ' + percent + '%, transparent ' + percent + '%)';
    }
});

Upload.fail(() => {
    if (Draw.draw) Draw.draw.classList = 'upload';
    if (Draw.save) Draw.save.querySelector('span.primary').style.backgroundImage = '';
});
