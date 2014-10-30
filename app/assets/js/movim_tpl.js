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
