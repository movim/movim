var VisioLink = {
    setSDP: function(remoteSDP) {
        localStorage.setItem('sdp', remoteSDP);
    },

    setCandidate: function(remoteCandidate) {
        var candidates = localStorage.getObject('candidates');
        if (!candidates) candidates = [];
        candidates.push(remoteCandidate);
    },

    reset: function() {
        VisioLink.window = null;
    },

    openVisio: function(from) {
        VisioLink.window = window.open('?visio/' + from, '', 'width=600,height=400,status=0,titlebar=0,toolbar=0,menubar=0');
    },

    setFrom: function(from) {
        VisioLink.from = from;
    }
}
