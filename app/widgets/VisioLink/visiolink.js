var VisioLink = {
    candidates: [],

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
