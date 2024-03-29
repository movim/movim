/**
 * Movim Events
 */


var MovimEvents = {
    eventsBody: {},
    eventsWindow: {},
    isFocused: false,
    isTouch: false,

    /**
     * @brief Register an event on the body
     * @param string event
     * @param string key
     * @param function action
     */
    registerBody: function (event, key, action) {
        if (MovimEvents.eventsBody[event] == undefined) {
            MovimEvents.eventsBody[event] = {};
        }

        MovimEvents.eventsBody[event][key] = action;
    },

    /**
     * @brief Register an event on the window
     * @param string event
     * @param string key
     * @param function action
     */
    registerWindow: function (event, key, action) {
        if (MovimEvents.eventsWindow[event] == undefined) {
            MovimEvents.eventsWindow[event] = {};
        }

        MovimEvents.eventsWindow[event][key] = action;
    },

    triggerBody: function (event, e) {
        if (MovimEvents.eventsBody[event] != undefined) {
            for (const [key, value] of Object.entries(MovimEvents.eventsBody[event])) {
                value(e);
            }
        }
    },

    triggerWindow: function (event, e) {
        if (MovimEvents.eventsWindow[event] != undefined) {
            for (const [key, value] of Object.entries(MovimEvents.eventsWindow[event])) {
                value(e);
            }
        }
    },

    focusEvent: function (e) {
        if (MovimEvents.isFocused) return;

        MovimEvents.isFocused = true;
        MovimEvents.triggerWindow('focus', e);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Window
    window.addEventListener('keydown', (e) => MovimEvents.triggerWindow('keydown', e), false);
    window.addEventListener('paste', (e) => MovimEvents.triggerWindow('paste', e), false);
    window.addEventListener('resize', (e) => MovimEvents.triggerWindow('resize', e), false);
    window.addEventListener('touchstart', (e) => MovimEvents.triggerWindow('touchstart', e), false);

    /**
     * The focus event doesn't seems to be triggered all the time ¯\_(ツ)_/¯
    */
    window.addEventListener('blur', (e) => {
        MovimEvents.isFocused = false;
        MovimEvents.triggerWindow('blur', e);
    }, false);
    window.addEventListener('focus', (e) => MovimEvents.focusEvent(e), false);
    window.addEventListener('mouseover', (e) => MovimEvents.focusEvent(e), false);

    // Body
    document.body.addEventListener('click', (e) => MovimEvents.triggerBody('click', e), false);
    document.body.addEventListener('keydown', (e) => MovimEvents.triggerBody('keydown', e), false);
    document.body.addEventListener('touchstart', (e) => MovimEvents.triggerBody('touchstart', e), false);
    document.body.addEventListener('touchend', (e) => MovimEvents.triggerBody('touchend', e), false);
});