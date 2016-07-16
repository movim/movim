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

// movim_append(div, text)
function movim_append(id, html)
{
    target = document.getElementById(id);
    if(target) {
        target.insertAdjacentHTML('beforeend', html);
    }
}
// movim_prepend(div, text)
function movim_prepend(id, html)
{
    target = document.getElementById(id);
    if(target) {
        target.insertAdjacentHTML('afterbegin', html);
    }
}
// movim_fill(div, text)
function movim_fill(id, html)
{
    target = document.getElementById(id);
    if(target) {
        target.innerHTML = html;
    }
}
// movim_delete(div)
function movim_delete(id)
{
    target = document.getElementById(id);
    if(target)
        target.parentNode.removeChild(target);
}
// movim_replace(id)
function movim_replace(id, html)
{
    target = document.getElementById(id);
    if(target) {
        var div = document.createElement('div');
        div.innerHTML = html;
        var element = div.firstChild;
        replacedNode = target.parentNode.replaceChild(element, target);
    }
}

var MovimTpl = {
    init : function() {
        if(document.getElementById('back') != null)
            MovimUtils.hideElement(document.getElementById('back'));
    },
    showPanel : function() {
        MovimUtils.addClass('main section > div:first-child:nth-last-child(2) ~ div', 'enabled');
        MovimTpl.scrollPanelTop();
    },
    hidePanel : function() {
        var selector = 'main section > div:first-child:nth-last-child(2) ~ div';
        var inner = document.querySelector(selector + ' div');

        MovimUtils.removeClass(selector, 'enabled');

        // Clear the right panel
        //if(inner != null) inner.innerHTML = '';
        //else document.querySelector(selector).innerHTML = '';
    },
    fill : function(selector, html) {
        target = document.querySelector(selector);
        if(target) {
            target.innerHTML = html;
        }
    },
    append : function(selector, html) {
        target = document.querySelector(selector);
        if(target) {
            target.insertAdjacentHTML('beforeend', html);
        }
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
    toggleMenu : function() {
        MovimUtils.toggleClass('body > nav', 'active');
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
    toggleActionButton : function() {
        MovimUtils.toggleClass('.button.action', 'active');
    },
    hideMenu : function() {
        MovimUtils.removeClass('body > nav', 'active');
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
    }
}

movim_add_onload(function() {
    MovimTpl.init();
    document.body.addEventListener('click', MovimTpl.toggleContextMenu, false);
});
