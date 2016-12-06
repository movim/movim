/**
 * Movim Javascript Template functions
 *
 * These are the default callback functions that users may (or may not) use.
 *
 * Note that all of them take only one parameter. Don't be fooled by this, the
 * expected parameter is actually an array containing the real parameters. These
 * are checked before use.
 *
 * Look at the comments for help.
 */

var MovimTpl = {
    init : function() {
        if(document.getElementById('back') != null)
            MovimUtils.hideElement(document.getElementById('back'));
    },
    append : function(selector, html) {
        target = document.querySelector(selector);
        if(target) {
            target.insertAdjacentHTML('beforeend', html);
        }
    },
    back : function() {
        // If the context menu is shown
        var cm = document.querySelector('ul.context_menu');
        if(cm != null && cm.className.includes('shown')) {
            MovimTpl.toggleContextMenu(document);
            // If a drawer is shown
        } else if(Drawer.filled()) {
            Drawer.clear();
            // If a dialog box is shown
        } else if(Dialog.filled()) {
            Dialog.clear();
            // If the menu is shown
        } else if(MovimUtils.hasClass('body > nav', 'active')) {
            MovimUtils.removeClass('body > nav', 'active');
            // If the panel is shown
        } else if(MovimTpl.isPanel()) {
            MovimTpl.hidePanel();
            window.history.back();
        } else {
            history.back();
        }
    },
    fill : function(selector, html) {
        target = document.querySelector(selector);
        if(target) {
            target.innerHTML = html;
        }
    },
    hideContextMenu : function() {
        MovimUtils.removeClass('ul.context_menu', 'shown');
    },
    hideMenu: function() {
        MovimUtils.removeClass('body > nav', 'active');
    },
    showPanel: function() {
        MovimUtils.addClass('main section', 'enabled');
        MovimTpl.scrollPanelTop();
    },
    hidePanel: function() {
        MovimUtils.removeClass('main section', 'enabled');
    },
    isPanel: function() {
        return MovimUtils.hasClass('main section', 'enabled');
    },
    isPanelScrolled: function() {
        var selector = document.querySelector('main section > div:first-child:nth-last-child(2) ~ div div');

        if(selector != null) {
            return (selector.scrollHeight - Math.floor(selector.scrollTop) <= selector.clientHeight + 3);
        }
    },
    prepend: function(selector, html) {
        target = document.querySelector(selector);
        if(target) {
            target.insertAdjacentHTML('afterbegin', html);
        }
    },
    remove: function(selector) {
        target = document.querySelector(selector);
        if(target)
            target.parentNode.removeChild(target);
    },
    replace: function (selector, html) {
        target = document.querySelector(selector);
        if(target) {
            var div = document.createElement('div');
            div.innerHTML = html;
            var element = div.firstChild;
            replacedNode = target.parentNode.replaceChild(element, target);
        }
    },
    scrollPanel : function() {
        var selector = document.querySelector('main section > div:first-child:nth-last-child(2) ~ div div');

        if(selector != null) {
            selector.scrollTop = selector.scrollHeight;
        }
    },
    scrollPanelTop : function() {
        var selector = document.querySelector('main section > div:first-child:nth-last-child(2) ~ div');

        if(selector != null) {
            selector.scrollTop = 0;
        }
    },
    toggleActionButton : function() {
        MovimUtils.toggleClass('.button.action', 'active');
    },
    toggleContextMenu : function(e) {
        var element = 'ul.context_menu';
        var classname = 'shown';

        if(document.querySelector(element) == null) {
            return;
        }

        if(document.querySelector('.show_context_menu').contains(e.target)) {
            MovimUtils.addClass(element, classname);
            return;
        }

        //if(!document.querySelector(element).contains(e.target))
        MovimUtils.removeClass(element, classname);
    },
    toggleMenu : function() {
        MovimUtils.toggleClass('body > nav', 'active');
    }
};

movim_add_onload(function() {
    MovimTpl.init();
    document.body.addEventListener('click', MovimTpl.toggleContextMenu, false);
});
