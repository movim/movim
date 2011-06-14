var yesman_called = false;
function yesman() {
    yesman_called = true;
}

var barfoo = false;
function toggle_barfoo() {
    barfoo = !barfoo;
}

var foobar = null;
function set_foobar(value) {
    foobar = value;
}

rpc = new MovimRPC();

tests.set('events', H({
    add_handler: function()
    {
        movim_add_event_handler('yesman', yesman);
        equals(events.get('yesman')[0], yesman);

        movim_add_event_handler('foobar', set_foobar);
        equals(events.get('foobar')[0], set_foobar);
    },

    emit_event: function()
    {
        movim_events_emit('yesman');
        assert(yesman_called);
    },

    emit_event_param: function()
    {
        movim_events_emit('foobar', 42);
        equals(foobar, 42);
    },

    multi_handlers: function()
    {
        barfoo = false;
        yesman_called = false;

        movim_add_event_handler('multi', yesman);
        movim_add_event_handler('multi', toggle_barfoo);

        movim_events_emit('multi');

        assert(barfoo);
        assert(yesman_called);
    },
}));