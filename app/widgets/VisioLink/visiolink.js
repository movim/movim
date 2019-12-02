var VisioLink = {
    /*setSDP: function(remoteSDP) {
        localStorage.setItem('sdp', remoteSDP);
    },

    setCandidate: function(remoteCandidate) {
        console.log('REMOTE CANDIDATE');
        console.log(remoteCandidate);
        var candidates = localStorage.getObject('candidates');
        if (!candidates) candidates = [];
        candidates.push(remoteCandidate);
    },*/

    reset: function() {
        VisioLink.window = null;
    },

    openVisio: function(from, id) {
        var idUrl = id ? '/' + id : '';
        VisioLink.window = window.open('?visio/' + from + idUrl, '', 'width=600,height=400,status=0,titlebar=0,toolbar=0,menubar=0');
    }/*,

    setFrom: function(from) {
        VisioLink.from = from;
    }*/
}
