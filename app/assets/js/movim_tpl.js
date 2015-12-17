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
            document.getElementById('back').style.display = 'none';

        MovimTpl.scrollHeaders();
    },
    scrollHeaders : function() {
        var headers = document.querySelectorAll('main > section > div > header');

        for(var i = 0, len = headers.length; i < len; ++i ) {
            var header = headers[i];

            header.parentNode.onscroll = function() {
                var header = this.querySelector('header');
                if(this.scrollTop == 0) {
                    movim_remove_class(header, 'scroll');
                } else {
                    movim_add_class(header, 'scroll');
                }
            }
        }
    },
    showPanel : function() {
        movim_add_class('main section > div:first-child:nth-last-child(2) ~ div', 'enabled');
        MovimTpl.scrollPanelTop();
        //MovimTpl.scrollHeaders();
    },
    hidePanel : function() {
        //Header_ajaxReset(CURRENT_PAGE);
        var selector = 'main section > div:first-child:nth-last-child(2) ~ div';
        var inner = document.querySelector(selector + ' div');

        movim_remove_class(selector, 'enabled');

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
    isPanel : function() {
        if(movim_has_class('main section > div:first-child:nth-last-child(2) ~ div', 'enabled')) {
            return true;
        } else {
            return false;
        }
    },
    isPanelScrolled : function() {
        var selector = document.querySelector('main section > div:first-child:nth-last-child(2) ~ div div');

        if(selector != null) {
            return (selector.scrollHeight - Math.floor(selector.scrollTop) === selector.clientHeight);
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
        movim_toggle_class('body > nav', 'active');
    },
    toggleContextMenu : function(e) {
        var element = 'ul.context_menu';
        var classname = 'shown';

        if(document.querySelector(element) == null) {
            return;
        }

        if(document.querySelector('.show_context_menu').contains(e.target)) {
            movim_add_class(element, classname);
            return;
        }

        //if(!document.querySelector(element).contains(e.target))
        movim_remove_class(element, classname);
    },
    toggleActionButton : function() {
        movim_toggle_class('.button.action', 'active');
    },
    hideContextMenu : function() {
        movim_remove_class('ul.context_menu', 'shown');
    },
    hideMenu : function() {
        movim_remove_class('body > nav', 'active');
    },
    back : function() {
        // If the contect menu is show
        var cm = document.querySelector('ul.context_menu');
        if(cm != null && cm.className.includes('shown')) {
            MovimTpl.toggleContextMenu(document);
        }
        // If a dialog box is show
        else if(Dialog.filled()) {
            Dialog.clear();
        // If the menu is shown
        } else if(movim_has_class('body > nav', 'active')) {
            movim_toggle_class('body > nav', 'active');
        // If the panel is shown
        } else if(MovimTpl.isPanel()) {
            MovimTpl.hidePanel();
        } else {
            window.history.back();
        }
    },
    getHeaderColor : function() {
        var header = document.querySelector('body main > header');
        return window.getComputedStyle(header).backgroundColor;
    }
}

movim_add_onload(function() {
    MovimTpl.init();
    document.body.addEventListener('click', MovimTpl.toggleContextMenu, false);
});
