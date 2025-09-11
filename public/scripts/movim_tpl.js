/**
 * Movim Javascript Template functions

 * Look at the comments for help.
 */

var MovimTpl = {
    startX: 0,
    startY: 0,
    translateX: 0,
    menuDragged: false,
    currentPage: '',
    currentAnchor: '',
    popAnchorKey: null,
    popAnchorAction: null,

    loadingPage: function () {
        document.body.classList.add('loading');
        document.body.classList.remove('finished');
    },
    finishedPage: function () {
        document.body.classList.add('finished');

        setTimeout(e => {
            document.body.classList.remove('loading', 'finished');
        }, 1000);
    },

    append: function (selector, html) {
        target = document.querySelector(selector);
        if (target) {
            target.insertAdjacentHTML('beforeend', html);
        }
    },
    appendAfter: function (selector, html) {
        target = document.querySelector(selector);
        if (target) {
            target.insertAdjacentHTML('afterend', html);
        }
    },
    pushAnchorState: function (key, action) {
        if (MovimTpl.popAnchorAction && key != MovimTpl.popAnchorKey) {
            MovimTpl.popAnchorAction();
        }

        window.history.pushState(null, null, '#' + key);
        MovimTpl.popAnchorKey = key;
        MovimTpl.popAnchorAction = action;
    },
    clearAnchorState: function () {
        if (MovimTpl.popAnchorAction) {
            MovimTpl.popAnchorAction();
        }

        MovimTpl.popAnchorKey = null;
        MovimTpl.popAnchorAction = null;
        window.history.replaceState(null, null, ' ');
    },
    fill: function (selector, html) {
        target = document.querySelector(selector);
        if (target) {
            target.innerHTML = html;
        }
    },
    showPanel: function () {
        MovimUtils.addClass('main', 'enabled');
        MovimUtils.addClass('ul#bottomnavigation', 'hidden');
    },
    hidePanel: function () {
        MovimUtils.removeClass('main', 'enabled');
        MovimUtils.removeClass('ul#bottomnavigation', 'hidden');
    },
    prepend: function (selector, html) {
        target = document.querySelector(selector);
        if (target) {
            target.insertAdjacentHTML('afterbegin', html);
        }
    },
    prependBefore: function (selector, html) {
        target = document.querySelector(selector);
        if (target) {
            target.insertAdjacentHTML('beforebegin', html);
        }
    },
    remove: function (selector) {
        target = document.querySelector(selector);
        if (target) {
            target.remove();
        }
    },
    replace: function (selector, html) {
        target = document.querySelector(selector);
        if (target) {
            var div = document.createElement('div');
            div.innerHTML = html;
            var element = div.firstChild;
            replacedNode = target.parentNode.replaceChild(element, target);
        }
    },
    showContextMenu: function() {
        var contextMenu = document.querySelector('ul.context_menu');
        contextMenu.classList.add('shown');
    },
    hideContextMenu: function (e) {
        var contextMenu = document.querySelector('ul.context_menu');
        if (contextMenu == null) return;

        if (!document.querySelector('.show_context_menu').contains(e.target)) {
            contextMenu.classList.remove('shown');
        }
    },
    toggleMenu: function () {
        document.querySelector('body > nav').classList.toggle('active');
    },
    touchEvents: function () {
        nav = document.querySelector('body > nav');
        mainDiv = document.querySelector('body > main > div:not(#chat_widget)');
        clientWidth = Math.abs(document.body.clientWidth);
        delay = 20;

        if (nav == null) return;

        mainDiv.addEventListener('touchmove', function (event) {
            moveX = event.targetTouches[0].pageX;
            MovimTpl.translateX = parseInt(moveX - MovimTpl.startX);

            if (nav && !nav.classList.contains('active')
                && MovimTpl.startX < clientWidth / 15
                && MovimTpl.startY > 56
                && MovimTpl.translateX < nav.offsetWidth + delay
                && MovimTpl.translateX > delay) {
                MovimTpl.menuDragged = true;
                event.stopPropagation();

                nav.style.transform = 'translateX(' + (-nav.offsetWidth + MovimTpl.translateX - delay) + 'px)';
            }
        }, true);

        nav.addEventListener('touchmove', function (event) {
            moveX = event.targetTouches[0].pageX;
            MovimTpl.translateX = parseInt(moveX - MovimTpl.startX);

            if (nav && nav.classList.contains('active') && MovimTpl.translateX - delay < 0) {
                MovimTpl.menuDragged = true;
                event.stopPropagation();
                nav.style.transform = 'translateX(' + (MovimTpl.translateX - delay) + 'px)';
            }
        }, true);
    }
};

MovimEvents.registerBody('touchstart', 'movimtpl', (event) => {
    document.querySelector('body > nav').classList.remove('moving');
    MovimTpl.startX = event.targetTouches[0].pageX;
    MovimTpl.startY = event.targetTouches[0].pageY;
});

MovimEvents.registerBody('touchend', 'movimtpl', (event) => {
    nav = document.querySelector('body > nav');
    nav.style.transform = '';
    nav.classList.add('moving');
    percent = MovimTpl.translateX / clientWidth;

    if (MovimTpl.menuDragged) {
        if (nav.classList.contains('active') && percent < -0.2) {
            nav.classList.remove('active');
        } else if (percent > 0.1) {
            nav.classList.add('active');
        }
    }

    MovimTpl.startX = MovimTpl.startY = MovimTpl.translateX = 0;
    MovimTpl.menuDragged = false;
});

MovimEvents.registerBody('click', 'movimtpl', (e) => MovimTpl.hideContextMenu(e));

MovimEvents.registerWindow('loaded', 'movimtpl', () => {
    if (MovimUtils.isMobile()) MovimTpl.touchEvents();

    MovimTpl.currentPage = window.location.pathname;
});

MovimEvents.registerWindow('popstate', 'movimtpl', (e) => {
    if (MovimTpl.popAnchorKey || e.target.location.hash != '') {
        MovimTpl.clearAnchorState();
        return;
    }

    if (e.target.location.pathname == MovimTpl.currentPage) return;

    MovimUtils.reload(e.target.location.href, true);

    MovimTpl.currentPage = e.target.location.pathname;
    MovimTpl.currentAnchor = window.location.hash.substring(1);
});
