var Publish = {
    setEmbed: function() {
        var embed = document.querySelector('input[name=embed]');
        embed.onpaste();
    },

    clearEmbed: function() {
        var embed = document.querySelector('input[name=embed]').value = '';
        MovimTpl.fill('#preview', '');
    },

    enableSend: function() {
        MovimUtils.removeClass('#button_send', 'disabled');
    },

    disableSend: function() {
        localStorage.removeItem('share_url');
        MovimUtils.addClass('#button_send', 'disabled');
    },

    enableContent: function() {
        MovimUtils.hideElement(document.getElementById('enable_content'));
        MovimUtils.showElement(document.getElementById('content_field'));
    },

    headerBack: function(server, node, ok) {
        // We check if the form is filled
        if(Publish.checkFilled() && ok == false) {
            Publish_ajaxFormFilled(server, node);
            return;
        }

        // We are on the news page
        if(typeof Post_ajaxClear === 'function') {
            Post_ajaxClear();
            MovimTpl.hidePanel();
        } else {
            MovimUtils.reload(BASE_URI + '?group/' + server + '/' + node);
        }
    },

    checkFilled: function() {
        var form = document.querySelector('form[name=post]');

        var i = 0;
        while(i < form.elements.length)
        {
            if(form.elements[i].type != 'hidden'
            && form.elements[i].type != 'checkbox'
            && form.elements[i].value != form.elements[i].defaultValue) {
                return true;
            }
            i++;
        }

        return false;
    },

    initEdit: function() {
        Publish.enableContent();
        Publish_ajaxEmbedTest(document.querySelector('#content_link input').value);
        MovimUtils.textareaAutoheight(document.querySelector('#content_field textarea'));
    }
}

Upload.attach(function() {
    var embed = document.querySelector('input[name=embed]');
    embed.value = Upload.get;
    embed.onpaste();
});

