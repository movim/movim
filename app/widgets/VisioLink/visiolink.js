var VisioLink = {
    openVisio: function(from, id, withVideo) {
        Notification.incomingAnswer();
        var idUrl = id ? '/' + id : '';
        var page = withVideo ? 'visio' : 'visioaudio';

        VisioLink.window = window.open('?' + page + '/' + from + idUrl, '', 'width=600,height=400,status=0,titlebar=0,toolbar=0,menubar=0');
    }
}
