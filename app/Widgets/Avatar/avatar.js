MovimWebsocket.attach(function() {
    Avatar_ajaxHttpGetCurrent();
    Avatar_ajaxGetAvatar();
});

var Avatar = {
    setPublic: function (value) {
        var input = document.querySelector('#avatar_public');
        if (input) {
            input.checked = value;
        }
    }
}
