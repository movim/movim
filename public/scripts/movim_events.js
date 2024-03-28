/**
 * Movim Events
 */


var MovimEvents = {
    events: {},

    register: function (event, key, action) {
        if (MovimEvents.events[event] == undefined) {
            MovimEvents.events[event] = {};
        }

        MovimEvents.events[event][key] = action;
    },

    trigger: function (event) {
        if (MovimEvents.events[event] != undefined) {
            for (var i = 0; i < MovimEvents.events[event].length; i++) {
                MovimEvents.events[event][i]();
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', function (event) {
    document.body.addEventListener('click', MovimEvents.trigger('click'), false);
    document.body.addEventListener('keydown', MovimEvents.trigger('keydown'), false);
});