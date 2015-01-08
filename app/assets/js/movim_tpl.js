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

var MovimTpl = {
    init : function() {
        if(document.getElementById('back') != null)
            document.getElementById('back').style.display = 'none';
    },
    showPanel : function() {
        movim_add_class('main section > div:first-child:nth-last-child(2) ~ div', 'enabled');
    },
    hidePanel : function() {
        Header_ajaxReset(CURRENT_PAGE);
        var selector = 'main section > div:first-child:nth-last-child(2) ~ div';
        var inner = document.querySelector(selector + ' div');

        movim_remove_class(selector, 'enabled');

        // Clear the right panel
        //if(inner != null) inner.innerHTML = '';
        //else document.querySelector(selector).innerHTML = '';
    },
    isPanel : function() {
        if(movim_has_class('main section > div:first-child:nth-last-child(2) ~ div', 'enabled')) {
            return true;
        } else {
            return false;
        }
    },
    scrollPanel : function() { // On for panel that are .contained
        var selector = document.querySelector('main section > div:first-child:nth-last-child(2) ~ div div');

        if(selector != null) {
            selector.scrollTop = selector.scrollHeight;
        }
    },
    toggleMenu : function() {
        movim_toggle_class('body > nav', 'active');
    },
    back : function() {
        if(movim_has_class('body > nav', 'active')) {
            movim_toggle_class('body > nav', 'active');
        } else if(MovimTpl.isPanel()) {
            MovimTpl.hidePanel();
        } else {
            window.history.back();
        }
    }
}

movim_add_onload(function() {
    MovimTpl.init();
});
