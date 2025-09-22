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
            for (const value of Object.values(MovimEvents.eventsBody[event])) {
                value(e);
            }
        }
    },

    triggerWindow: function (event, e) {
        if (MovimEvents.eventsWindow[event] != undefined) {
            for (const value of Object.values(MovimEvents.eventsWindow[event])) {
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

document.addEventListener('DOMContentLoaded', (e) => {
    // Window
    window.addEventListener('load', (e) => MovimEvents.triggerWindow('loaded', e), false);
    window.addEventListener('keydown', (e) => MovimEvents.triggerWindow('keydown', e), false);
    window.addEventListener('paste', (e) => MovimEvents.triggerWindow('paste', e), false);
    window.addEventListener('resize', (e) => MovimEvents.triggerWindow('resize', e), false);
    window.addEventListener('focus', (e) => MovimEvents.triggerWindow('focus', e), false);
    window.addEventListener('online', (e) => MovimEvents.triggerWindow('online', e), false);
    window.addEventListener('offline', (e) => MovimEvents.triggerWindow('offline', e), false);
    window.addEventListener('popstate', (e) => MovimEvents.triggerWindow('popstate', e), false);
    window.addEventListener('touchstart', (e) => MovimEvents.triggerWindow('touchstart', e), false);
    window.addEventListener('touchstart', function () { MovimEvents.isTouch = true; }, { once: true });

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
    document.body.addEventListener('touchmove', (e) => MovimEvents.triggerBody('touchmove', e), false);
    document.body.addEventListener('dragover', (e) => MovimEvents.triggerBody('dragover', e), false);
    document.body.addEventListener('drop', (e) => MovimEvents.triggerBody('drop', e), false);
    document.body.addEventListener('dragleave', (e) => MovimEvents.triggerBody('dragleave', e), false);
}, false);