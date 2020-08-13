var MovimFavicon = {
    originalUrl: null,
    sizes: '128x128',

    counter: function(counter) {
        link = document.querySelector('link[sizes="' + MovimFavicon.sizes + '"]');

        if (counter > 0) {
            large = counter > 9;
            if (counter > 99) counter = '+';

            canvas = document.createElement("canvas");
            ctx = canvas.getContext('2d');
            faviconImage = new Image();

            canvas.width = 32;
            canvas.height = 32;

            faviconImage.onload = function() {
                ctx.drawImage(faviconImage, 0, 0, 32, 32);

                ctx.textAlign = 'center';
                ctx.font = '19px bold serif';

                radius = 5;
                x = large ? 4 : 12;
                y = 12;
                w = large ? 28 : 20;
                h = 20;

                var r = x + w;
                var b = y + h;

                ctx.beginPath();
                ctx.fillStyle = '#FF5722';
                ctx.moveTo(x+radius, y);
                ctx.lineTo(r-radius, y);
                ctx.quadraticCurveTo(r, y, r, y+radius);
                ctx.lineTo(r, y+h-radius);
                ctx.quadraticCurveTo(r, b, r-radius, b);
                ctx.lineTo(x+radius, b);
                ctx.quadraticCurveTo(x, b, x, b-radius);
                ctx.lineTo(x, y+radius);
                ctx.quadraticCurveTo(x, y, x+radius, y);
                ctx.fill();

                ctx.fillStyle = "#fff";
                counterTextX = large ? 18 : 22;
                ctx.fillText( counter , counterTextX , 29, w);

                MovimFavicon.set(canvas.toDataURL());
            }

            faviconImage.src = MovimFavicon.originalUrl;
        }  else {
            MovimFavicon.set(MovimFavicon.originalUrl);
        }
    },

    set: function(url) {
        document.querySelector('link[sizes="' + MovimFavicon.sizes + '"]').remove();

        // We create a favicon link
        var linkElement = document.createElement('link');
        linkElement.rel = 'icon';
        linkElement.sizes = MovimFavicon.sizes;
        linkElement.type = 'image/png';
        linkElement.href = url;

        document.head.appendChild(linkElement);
    },

    init: function() {
        link = document.querySelector('link[sizes="' + MovimFavicon.sizes + '"]');
        MovimFavicon.originalUrl = link.href;
        MovimFavicon.set(link.href);
    }
}

movimAddOnload(function() {
    MovimFavicon.init();
});