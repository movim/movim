var VisioLink = {
    openVisio: function(from, id) {
        Notification.incomingAnswer();
        var idUrl = id ? '/' + id : '';
        VisioLink.window = window.open('?visio/' + from + idUrl, '', 'width=600,height=400,status=0,titlebar=0,toolbar=0,menubar=0');
    }
}
