var Publish = {
    init: function() {
        if(localStorage.getItem('share_url')) {
            Publish_ajaxCreateBlog();
            MovimTpl.showPanel();
        }
    },

    setEmbed: function() {
        if(localStorage.getItem('share_url')) {
            var embed = document.querySelector('input[name=embed]');
            embed.value = localStorage.getItem('share_url');
            embed.onpaste();
            localStorage.removeItem('share_url');
        }
    },

    enableSend: function() {
        movim_remove_class('#button_send', 'disabled');
    },

    disableSend: function() {
        movim_add_class('#button_send', 'disabled');
    },

    headerBack: function(server, node, ok) {
        // We check if the form is filled
        if(Publish.checkFilled() && ok == false) {
            Publish_ajaxFormFilled('{$server}', '{$node}');
            return;
        }

        // We are on the news page
        if(typeof Post_ajaxClear === 'function') {
            Post_ajaxClear();
            Header_ajaxReset('news');
            MovimTpl.hidePanel();
        } else {
            Group_ajaxGetItems(server, node);
        }
    },

    checkFilled: function() {
        var form = document.querySelector('form[name=post]');

        var i = 0;
        while(i < form.elements.length)
        {
            if(form.elements[i].type != 'hidden'
            && form.elements[i].value != form.elements[i].defaultValue) {
                return true;
            }
            i++;
        }

        return false;
    }
}

MovimWebsocket.attach(function() {
    Publish.init();
});
