/**
 * Movim Javascript Template functions

 * Look at the comments for help.
 */

var MovimTpl = {
    dragged : false,
    moving : false,
    percent : false,
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
            Preview_ajaxHide();
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
                Chat_ajaxGet();
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
        if (target)
            target.parentNode.removeChild(target);
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

        if (nav == null) return;

        nav.addEventListener('touchstart', function(event) {
            //event.preventDefault();

            startX = event.targetTouches[0].pageX;
            startY = event.targetTouches[0].pageY;

            if (
            (
                (startX < document.body.clientWidth/35 && startY > 56)
                ||
                (nav.classList.contains('active') && startX > document.body.clientWidth - 50)
            )
            && MovimTpl.dragged == false) {
                nav.classList.add('moving');
                MovimTpl.dragged = true;
            }
        }, true);

        nav.addEventListener('touchmove', function(event) {
            //event.preventDefault();

            moveX = event.targetTouches[0].pageX;

            if (MovimTpl.dragged) {
                event.preventDefault();
                event.stopPropagation();

                position = moveX - document.body.clientWidth;

                MovimTpl.percent = 1 - Math.abs(moveX) / Math.abs(document.body.clientWidth);
                nav.style.transform = 'matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, '+position+', 0, 0, 1)';
            }
        }, true);

        nav.addEventListener('touchend', function(event) {
            event.preventDefault();

            nav.style.transform = '';

            if (MovimTpl.dragged) {
                nav.classList.remove('moving');

                if (!nav.classList.contains('active')
                && MovimTpl.percent < 0.80) {
                    nav.classList.add('active');
                } else if (MovimTpl.percent > 0.20) {
                    nav.classList.remove('active');
                }
            }

            MovimTpl.dragged = false;
        }, true);
    }
};

movimAddOnload(function() {
    MovimTpl.touchEvents();
    document.body.addEventListener('click', MovimTpl.toggleContextMenu, false);
});
