var Notifications = {
    setCounters : function(counter) {
        var counters = document.querySelectorAll('.counter.notifications:not([data-key])');
        for (i = 0; i < counters.length; i++) {
            counters[i].innerHTML = counter;
        }
    }
}

MovimWebsocket.attach(function() {
    Notifications_ajaxSetCounter();
});
