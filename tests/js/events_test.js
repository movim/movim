var yesman_called = false;
var yesman = function() {yesman_called = true;};

tests.set('events', H({
    add_handler: function()
    {
        movim_add_event_handler('toto', yesman);
        equals(events.get('toto')[0], yesman);
    },

    emit_event: function()
    {
        movim_events_emit('toto');
        assert(yesman_called);
    },
}));