var MovimFavicon = {
    originalUrl: null,
    originalIcon: null,
    sizes: '128x128',

    counter: function (counterTab1, counterTab2) {
        var counter = counterTab1 + counterTab2;

        link = document.querySelector('link[sizes="' + MovimFavicon.sizes + '"]');

        canvas = document.createElement("canvas");
        ctx = canvas.getContext('2d');

        canvas.width = 32;
        canvas.height = 32;

        ctx.drawImage(MovimFavicon.originalIcon, 0, 0, 32, 32);

        if (counter > 0) {
            large = counter > 9;
            if (counter > 99) {
                counter = '+';
                large = false;
            }

            ctx.textAlign = 'center';
            ctx.font = 'bold 18px Roboto';

            radius = 5;
            x = large ? 4 : 12;
            y = 12;
            w = large ? 28 : 20;
            h = 20;

            var r = x + w;
            var b = y + h;

            ctx.beginPath();
            ctx.fillStyle = counterTab2 > 0
                ? getComputedStyle(document.body).getPropertyValue('--movim-red')
                : 'rgb(' + getComputedStyle(document.body).getPropertyValue('--movim-main') + ')';
            ctx.moveTo(x + radius, y);
            ctx.lineTo(r - radius, y);
            ctx.quadraticCurveTo(r, y, r, y + radius);
            ctx.lineTo(r, y + h - radius);
            ctx.quadraticCurveTo(r, b, r - radius, b);
            ctx.lineTo(x + radius, b);
            ctx.quadraticCurveTo(x, b, x, b - radius);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
            ctx.fill();

            ctx.fillStyle = "#fff";
            counterTextX = large ? 18 : 22;
            ctx.fillText(counter, counterTextX, 28, w);
        }

        MovimFavicon.set(canvas.toDataURL());
    },

    set: function (url) {
        link = document.querySelector('link[sizes="' + MovimFavicon.sizes + '"]');
        link.href = url;
    },

    init: function () {
        link = document.querySelector('link[sizes="' + MovimFavicon.sizes + '"]');
        MovimFavicon.originalUrl = link.href;
        MovimFavicon.originalIcon = new Image();
        MovimFavicon.originalIcon.src = MovimFavicon.originalUrl;
    }
}

movimAddOnload(function () {
    MovimFavicon.init();
});