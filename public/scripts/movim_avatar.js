var MovimAvatar = {
    file : function(files, formname, width = 512, height = 512) {
        var f = files[0];
        if (!f.type.match(/image.*/)) {
          console.log("Not a picture !");
          MovimAvatar.clear(formname);
        } else {
            var reader = new FileReader();
            reader.readAsDataURL(f);

            reader.onload = function (ev) {
                MovimUtils.getOrientation(f, function(orientation) {
                    MovimAvatar.preview(ev.target.result, orientation, formname, width, height);
                });
            };
        };
    },
    clear : function(formname) {
        document.querySelector('form[name=' + formname + '] img').src = '';
        document.querySelector('form[name=' + formname + '] input[name="photobin"]').value = '';
    },
    preview : function(src, orientation, formname, setWidth, setHeight) {
        var canvas = document.createElement('canvas');
        width = canvas.width = setWidth;
        height = canvas.height = setHeight;

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

            MovimUtils.drawImageProp(ctx, image);

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
