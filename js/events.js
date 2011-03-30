/**
 * MOVIM javascript events system.
 */
var events = H();

function movim_add_event_handler(event_type, func)
{
    if(!events.haskey(event_type)) {
        events.set(event_type, new Array(func));
    } else {
        events.set(event_type, events.get(event_type).push(func));
    }
}

function movim_events_emit(event_type)
{
    if(events.haskey(event_type)) {
        var myevents = events.get(event_type);
        for(var i in myevents) {
            try {
                myevents[i]();
            }
            catch(err)
            {
                // nothing.
            }
        }
    }
}