var MovimAvatar = {
    file : function(files, formname) {
        var f = files[0];
        if (!f.type.match(/image.*/)) {
          console.log("Not a picture !");
          MovimAvatar.clear(formname);
        } else {
            var reader = new FileReader();
            reader.readAsDataURL(f);

            reader.onload = function (ev) {
                MovimUtils.getOrientation(f, function(orientation) {
                    MovimAvatar.preview(ev.target.result, orientation, formname);
                });
            };
        };
    },
    clear : function(formname) {
        document.querySelector('form[name=' + formname + '] img').src = '';
        document.querySelector('form[name=' + formname + '] input[name="photobin"]').value = '';
    },
    preview : function(src, orientation, formname) {
        var canvas = document.createElement('canvas');
        width = height = canvas.width = canvas.height = 350;

        var image = new Image();
        image.src = src;
        image.onload = function() {
            ctx = canvas.getContext("2d");

            switch (orientation) {
                case 2: ctx.transform(-1, 0, 0, 1, width, 0); break;
                case 3: ctx.transform(-1, 0, 0, -1, width, height ); break;
                case 4: ctx.transform(1, 0, 0, -1, 0, height ); break;
                case 5: ctx.transform(0, 1, 1, 0, 0, 0); break;
                case 6: ctx.transform(0, 1, -1, 0, height , 0); break;
                case 7: ctx.transform(0, -1, -1, 0, height , width); break;
                case 8: ctx.transform(0, -1, 1, 0, 0, width); break;
                default: ctx.transform(1, 0, 0, 1, 0, 0);
            }

            if (image.width == image.height) {
                ctx.drawImage(image, 0, 0, width, height);
            } else {
                minVal = parseInt(Math.min(image.width, image.height));
                if (image.width > image.height) {
                    ctx.drawImage(image, (parseInt(image.width) - minVal) / 2, 0, minVal, minVal, 0, 0, width, height);
                } else {
                    ctx.drawImage(image, 0, (parseInt(image.height) - minVal) / 2, minVal, minVal, 0, 0, width, height);
                }
            }

            var base64 = canvas.toDataURL('image/jpeg', 0.95);

            var preview = document.querySelector('form[name=' + formname + '] img');

            var list = document.querySelector('form[name=' + formname + '] ul');
            if (list) list.classList.add('hide');

            var input = document.querySelector('form[name=' + formname + '] input[name="photobin"]');

            if (preview.className == 'error') preview.className = '';

            preview.src = base64;

            var bin = base64.split(",");
            input.value = bin[1];
        }
    }
}
