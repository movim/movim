/**
 * Movim Javascript Template functions

 * Look at the comments for help.
 */

var MovimTpl = {
    startX: 0,
    startY: 0,
    translateX: 0,
    menuDragged: false,

    append : function(selector, html) {
        target = document.querySelector(selector);
        if (target) {
            target.insertAdjacentHTML('beforeend', html);
        }
    },
    appendAfter : function(selector, html) {
        target = document.querySelector(selector);
        if (target) {
            target.insertAdjacentHTML('afterend', html);
        }
    },
    back : function() {
        // If the context menu is shown
        var cm = document.querySelector('ul.context_menu');
        if (cm != null && cm.className.includes('shown')) {
            MovimTpl.toggleContextMenu(document);
        } else if (typeof Draw == 'object' && Draw.draw != undefined && Draw.draw.classList.contains('open')) {
            Draw.draw.classList.remove('open');
        } else if (typeof Snap == 'object' && Snap.snap != undefined && Snap.snap.className !== '') {
            if (Snap.snap.classList.contains('upload')) {
                Snap.snap.className = 'shoot';
                Snap.video.play();
            } else {
                Snap.end();
            }
        } else if (document.querySelector('#preview')
         && document.querySelector('#preview').innerHTML != '') {
            Preview_ajaxHttpHide();
        } else if (Drawer.filled()) {
            Drawer.clear();
            // If a dialog box is shown
        } else if (Dialog.filled()) {
            Dialog.clear();
            // If the menu is shown
        } else if (document.querySelector('body > nav').classList.contains('active')) {
            document.querySelector('body > nav').classList.remove('active');
            // If the panel is shown
        } else if (document.querySelector('main').classList.contains('enabled')) {
            if (MovimUtils.urlParts().page == 'chat') {
                Chat.get();
            } else {
                MovimTpl.hidePanel();
                window.history.back();
            }
        } else {
            history.back();
        }
    },
    fill : function(selector, html) {
        target = document.querySelector(selector);
        if (target) {
            target.innerHTML = html;
        }
    },
    hideMenu: function() {
        MovimUtils.removeClass('body > nav', 'active');
    },
    showPanel: function() {
        MovimUtils.addClass('main', 'enabled');
        MovimUtils.addClass('ul#bottomnavigation', 'hidden');
    },
    hidePanel: function() {
        MovimUtils.removeClass('main', 'enabled');
        MovimUtils.removeClass('ul#bottomnavigation', 'hidden');
    },
    prepend: function(selector, html) {
        target = document.querySelector(selector);
        if (target) {
            target.insertAdjacentHTML('afterbegin', html);
        }
    },
    prependBefore: function(selector, html) {
        target = document.querySelector(selector);
        if (target) {
            target.insertAdjacentHTML('beforebegin', html);
        }
    },
    remove: function(selector) {
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
    toggleContextMenu : function(e) {
        var contextMenu = document.querySelector('ul.context_menu');
        if (contextMenu == null) return;

        if (document.querySelector('.show_context_menu').contains(e.target)) {
            contextMenu.classList.add('shown');
        } else {
            contextMenu.classList.remove('shown');
        }
    },
    toggleMenu : function() {
        document.querySelector('body > nav').classList.toggle('active');
    },
    touchEvents: function() {
        nav = document.querySelector('body > nav');
        mainDiv = document.querySelector('body > main > div:not(#chat_widget)');
        clientWidth = Math.abs(document.body.clientWidth);
        delay = 20;

        if (nav == null) return;

        document.body.addEventListener('touchstart', function(event) {
            MovimTpl.startX = event.targetTouches[0].pageX;
            MovimTpl.startY = event.targetTouches[0].pageY;
            nav.classList.remove('moving');
        }, true);

        mainDiv.addEventListener('touchmove', function(event) {
            moveX = event.targetTouches[0].pageX;
            MovimTpl.translateX = parseInt(moveX - MovimTpl.startX);

            if (!nav.classList.contains('active')
                    && MovimTpl.startX < clientWidth/15
                    && MovimTpl.startY > 56
                    && MovimTpl.translateX < nav.offsetWidth + delay
                    && MovimTpl.translateX > delay) {
                MovimTpl.menuDragged = true;
                event.stopPropagation();

                nav.style.transform = 'translateX(' + (-nav.offsetWidth + MovimTpl.translateX - delay) + 'px)';
            }
        }, true);

        nav.addEventListener('touchmove', function(event) {
            moveX = event.targetTouches[0].pageX;
            MovimTpl.translateX = parseInt(moveX - MovimTpl.startX);

            if (nav.classList.contains('active') && MovimTpl.translateX - delay < 0) {
                MovimTpl.menuDragged = true;
                event.stopPropagation();
                nav.style.transform = 'translateX(' + (MovimTpl.translateX - delay) + 'px)';
            }
        }, true);

        document.body.addEventListener('touchend', function(event) {
            nav.classList.add('moving');
            nav.style.transform = '';

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
        }, true);
    }
};

movimAddOnload(function() {
    if (MovimUtils.isMobile()) MovimTpl.touchEvents();
    document.body.addEventListener('click', MovimTpl.toggleContextMenu, false);
    /*window.addEventListener('popstate', e => {
        // Prevent empty href to trigger the event
        if (window.location.href.substring(window.location.href.length -1) != '#') {
            MovimTpl.back()
        }
    });*/
});
