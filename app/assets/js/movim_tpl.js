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

        MovimTpl.scrollHeaders();
    },
    scrollHeaders : function() {
        /*var headers = document.querySelectorAll('main > section > div > header');

        for(var i = 0, len = headers.length; i < len; ++i ) {
            var header = headers[i];

            header.parentNode.onscroll = function() {
                var header = this.querySelector('header');
                if(this.scrollTop == 0) {
                    MovimUtils.removeClass(header, 'scroll');
                } else {
                    MovimUtils.addClass(header, 'scroll');
                }
            }
        }*/
    },
    append : function(selector, html) {
        target = document.querySelector(selector);
        if(target) {
            target.insertAdjacentHTML('beforeend', html);
        }
    },
    back : function() {
        // If the contect menu is show
        var cm = document.querySelector('ul.context_menu');
        if(cm != null && cm.className.includes('shown')) {
            MovimTpl.toggleContextMenu(document);
            // If a drawer is show
        } else if(Drawer.filled()) {
            Drawer.clear();
            // If a dialog box is show
        } else if(Dialog.filled()) {
            Dialog.clear();
            // If the menu is shown
        } else if(MovimUtils.hasClass('body > nav', 'active')) {
            MovimUtils.toggleClass('body > nav', 'active');
            // If the panel is shown
        } else if(MovimTpl.isPanel()) {
            MovimTpl.hidePanel();
        } else {
            window.history.back();
        }
    },
    remove: function(id) {
        target = document.getElementById(id);
        if(target)
            target.parentNode.removeChild(target);
    },
    replace: function(id, html) {
        target = document.getElementById(id);
        if(target) {
            var div = document.createElement('div');
            div.innerHTML = html;
            var element = div.firstChild;
            replacedNode = target.parentNode.replaceChild(element, target);
        }
    },
    fill : function(selector, html) {
        target = document.querySelector(selector);
        if(target) {
            target.innerHTML = html;
        }
    },
    getHeaderColor : function() {
        var header = document.querySelector('body main > header');
        return window.getComputedStyle(header).backgroundColor;
    },
    hideContextMenu : function() {
        MovimUtils.removeClass('ul.context_menu', 'shown');
    },
    hideMenu : function() {
        MovimUtils.removeClass('body > nav', 'active');
    },
    hidePanel : function() {
        //Header_ajaxReset(CURRENT_PAGE);
        var selector = 'main section > div:first-child:nth-last-child(2) ~ div';
        var inner = document.querySelector(selector + ' div');

        MovimUtils.removeClass(selector, 'enabled');

        // Clear the right panel
        //if(inner != null) inner.innerHTML = '';
        //else document.querySelector(selector).innerHTML = '';
    },
    isPanel : function() {
        if(MovimUtils.hasClass('main section > div:first-child:nth-last-child(2) ~ div', 'enabled')) {
            return true;
        } else {
            return false;
        }
    },
    isPanelScrolled : function() {
        var selector = document.querySelector('main section > div:first-child:nth-last-child(2) ~ div div');

        if(selector != null) {
            return (selector.scrollHeight - Math.floor(selector.scrollTop) <= selector.clientHeight + 3);
        }
    },
    prepend: function(id, html)
    {
        target = document.getElementById(id);
        if(target) {
            target.insertAdjacentHTML('afterbegin', html);
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
    showPanel : function() {
        MovimUtils.addClass('main section > div:first-child:nth-last-child(2) ~ div', 'enabled');
        MovimTpl.scrollPanelTop();
        //MovimTpl.scrollHeaders();
    }
};

movim_add_onload(function() {
    MovimTpl.init();
    document.body.addEventListener('click', MovimTpl.toggleContextMenu, false);
});
