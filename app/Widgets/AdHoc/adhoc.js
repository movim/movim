var AdHoc = {
    refresh: function () {
        var items = document.querySelectorAll('.adhoc_widget .actions li:not(.subheader)');
        var i = 0;

        while (i < items.length) {
            items[i].onclick = function () {
                Drawer.clear();
                AdHoc_ajaxCommand(this.dataset.jid, this.dataset.node);
            };

            i++;
        }
    },
    initForm: function () {
        var textareas = document.querySelectorAll('#dialog form[name=command] textarea');
        var i = 0;

        while (i < textareas.length) {
            MovimUtils.textareaAutoheight(textareas[i]);
            i++;
        }

        var form = document.querySelector('#dialog form[name=command]');

        if (form) {
            form.addEventListener('input', e => AdHoc.checkFormValidity());
        }

        AdHoc.checkFormValidity();
    },
    submit: function (jid, action) {
        var form = document.querySelector('#dialog form[name=command]');
        AdHoc_ajaxSubmit(
            jid,
            form.dataset.node,
            MovimUtils.formToJson('command'),
            form.dataset.sessionid,
            action
        );
    },
    checkFormValidity: function () {
        var form = document.querySelector('#dialog form[name=command]');
        var action = document.querySelector('#dialog #adhoc_action');

        if (form && action) {
            // For hat pick dialogs, also require a hat to be selected
            var needsPick = form.querySelector('.hat-pick-item') !== null;
            var picked = needsPick
                ? document.getElementById('hat_selected_field')?.value !== ''
                : true;

            if (form.checkValidity() && picked) {
                action.classList.remove('disabled');
            } else {
                action.classList.add('disabled');
            }
        }
    },

    // Called when a hat chip is clicked in the destroy/assign/remove dialogs.
    hatPick: function (item) {
        document.querySelectorAll('.hat-pick-item').forEach(function (el) {
            el.classList.remove('enabled');
        });
        item.classList.add('enabled');

        var hidden = document.getElementById('hat_selected_field');
        hidden.name  = item.dataset.fieldvar;
        hidden.value = item.dataset.value;

        AdHoc.checkFormValidity();
    },

    // Helpers scoped to the hat-create dialog.
    hatCreate: {
        // Called by oninput on the color picker.
        onColorChange: function (hex) {
            var hue = AdHoc.hatCreate.hexToHue(hex);
            document.getElementById('hat_hue').value = hue;

            var icon = document.getElementById('hat_preview_icon');
            if (icon) icon.style.color = hex;

            AdHoc.checkFormValidity();
        },

        updatePreview: function () {
            var titleEl   = document.getElementById('hat_title');
            var previewEl = document.getElementById('hat_preview_title');
            if (titleEl && previewEl) {
                previewEl.textContent = titleEl.value.trim() || '…';
            }
            AdHoc.checkFormValidity();
        },

        // Extract hue (0-360) from a CSS #rrggbb hex string.
        hexToHue: function (hex) {
            var r = parseInt(hex.slice(1, 3), 16) / 255;
            var g = parseInt(hex.slice(3, 5), 16) / 255;
            var b = parseInt(hex.slice(5, 7), 16) / 255;
            var max = Math.max(r, g, b), min = Math.min(r, g, b);
            var h = 0;
            if (max !== min) {
                var d = max - min;
                switch (max) {
                    case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
                    case g: h = ((b - r) / d + 2) / 6; break;
                    case b: h = ((r - g) / d + 4) / 6; break;
                }
            }
            return Math.round(h * 360 * 1000) / 1000;
        }
    }
}

MovimWebsocket.attach(function () {
    var parts = MovimUtils.urlParts();

    if (parts.page === "configuration") {
        AdHoc_ajaxGet();
    }
});
