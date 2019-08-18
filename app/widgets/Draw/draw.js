var Draw = {
    BLACK: '#222222',
    BLUE: '#3F51B5',
    RED: '#e91e63',
    GREEN: '#689F38',
    PURPLE: '#9C27B0',
    SMALL: 2,
    MEDIUM: 4,
    BIG: 6,

    canvas: null,
    canvasbg: null,
    ctx: null,
    draw: null,

    // MouseEvents for drawing
    drawing: false,
    mousePos: { x: 0, y: 0 },
    lastPos: this.mousePos,

    init: function () {
        Draw.draw = document.getElementById('draw');
        // Set up the canvas
        Draw.canvas = document.getElementById('draw-canvas');
        Draw.canvas.width = document.body.clientWidth;
        Draw.canvas.height = document.body.clientHeight;

        Draw.canvasbg = document.getElementById('draw-background');
        Draw.canvasbg.width = document.body.clientWidth;
        Draw.canvasbg.height = document.body.clientHeight;

        Draw.ctx = Draw.canvas.getContext('2d');
        Draw.ctx.strokeStyle = Draw.BLACK;
        Draw.ctx.lineWidth = Draw.SMALL;
        Draw.ctx.lineCap = 'round';

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
                clientY: touch.clientY
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
        const save = document.getElementById('draw-save');
        save.addEventListener('click', (e) => {
            const rect = Draw.canvas.getBoundingClientRect();
            const finalCanvas = document.createElement('canvas');
            finalCanvas.setAttribute('width', 320);
            finalCanvas.setAttribute('height', 160);

            const bgimg = document.getElementById('background');
            if(bgimg){
                const finalctx = finalCanvas.getContext('2d');
                finalctx.drawImage(bgimg, 0, 0, rect.width, rect.height);
            }

            finalctx.drawImage(Draw.canvas, 0, 0);
            document.body.appendChild(finalCanvas);
        })

        // Use the eraser
        const eraser = document.querySelector('.draw-eraser');
        eraser.addEventListener('click', (e) => {
            Draw.ctx.globalCompositeOperation = 'destination-out';
            Draw.ctx.strokeStyle = 'rgba(0,0,0,1)';
        }, false);

        // Change pencil color
        const colors = document.querySelectorAll('.draw-colors li');
        for (let i = 0; i < colors.length; i++) {
            colors[i].addEventListener('click', (e) => {
                Draw.ctx.globalCompositeOperation = 'source-over';
                let color;
                switch (e.target.getAttribute('data-color')) {
                    case 'blue':
                        color = Draw.BLUE;
                        break;
                    case 'red':
                        color = Draw.RED;
                        break;
                    case 'green':
                        color = Draw.GREEN;
                        break;
                    case 'purple':
                        color = Draw.PURPLE;
                        break;
                    default:
                        color = Draw.BLACK;
                }
                Draw.ctx.strokeStyle = color;
            });
        }

        // Change pencil thickness
        const widths = document.querySelectorAll('.draw-widths li');
        for (let i = 0; i < widths.length; i++) {
            widths[i].addEventListener('click', (e) => {
                let width;
                switch (e.target.getAttribute('data-width')) {
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
                Draw.ctx.lineWidth = width
            });
        }
        const drawback = document.querySelector('#draw #drawback');
        drawback.addEventListener('click', () => {
            Draw.draw.classList = '';
        });

        const drawclose = document.querySelector('#draw #drawclose');
        drawclose.addEventListener('click', () => {
            Draw.draw.classList = '';
        });

        // When all is ready, show the panel
        Draw.draw.classList.add('init');
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