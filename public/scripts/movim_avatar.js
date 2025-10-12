var MovimAvatar = {
    file : function(files, formname, width = 384, height = 384) {
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
        var image = new Image();
        image.src = src;

        image.onload = function() {
            var canvas = document.createElement('canvas');
            width = canvas.width = setWidth;
            height = canvas.height = setHeight;
            ctx = canvas.getContext("2d");

            MovimUtils.applyOrientation(ctx, orientation, width, height)
            MovimUtils.drawImageProp(ctx, image);

            var base64 = canvas.toDataURL('image/jpeg', 0.80);

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
