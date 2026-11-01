var MovimAvatar = {
    form: function (formname) {
        return document.querySelector(`form[name="${formname}"]`);
    },

    file: async function (files, formname, width = 384, height = 384) {
        const file = files?.[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            MovimAvatar.clear(formname);
            return;
        }

        try {
            MovimAvatar.preview(await MovimUtils.loadImage(file), formname, width, height);
        } catch (e) {
            MovimAvatar.clear(formname);
        }
    },

    clear: function (formname) {
        const form = MovimAvatar.form(formname);
        form.querySelector('img').src = '';
        form.querySelector('input[name="photobin"]').value = '';
    },

    preview: function (image, formname, width, height) {
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        ctx.imageSmoothingQuality = 'high';

        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, width, height);

        MovimUtils.drawImageProp(ctx, image);

        const base64 = canvas.toDataURL('image/jpeg', 0.80);

        const form = MovimAvatar.form(formname);
        const preview = form.querySelector('img');

        form.querySelector('ul')?.classList.add('hide');
        preview.classList.remove('error');
        preview.src = base64;

        form.querySelector('input[name="photobin"]').value = base64.slice(base64.indexOf(',') + 1);
    }
};