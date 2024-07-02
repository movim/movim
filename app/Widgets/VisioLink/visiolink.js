var VisioLink = {
    openVisio: function(from, id, withVideo) {
        Notif.incomingAnswer();
        var idUrl = id ? '/' + id : '';
        var page = withVideo ? 'visio' : 'visioaudio';

        VisioLink.window = window.open(BASE_URI + page + '/' + btoa(from) + idUrl, '', 'width=600,height=400,status=0,titlebar=0,toolbar=0,menubar=0');
    }
}
