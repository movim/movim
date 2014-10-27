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
function movim_append(params)
{
    if(params.length < 2) {
        return;
    }

    target = document.getElementById(params[0]);
    if(target) {
        target.insertAdjacentHTML('beforeend', params[1]);
    }
}
// movim_prepend(div, text)
function movim_prepend(params)
{
    if(params.length < 2) {
        return;
    }

    target = document.getElementById(params[0]);
    if(target) {
        target.insertAdjacentHTML('afterbegin', params[1]);
    }
}
// movim_fill(div, text)
function movim_fill(params)
{
    if(params.length < 2) {
        return;
    }

    target = document.getElementById(params[0]);
    if(target) {
        target.innerHTML = params[1];
    }
}
// movim_delete(div)
function movim_delete(params)
{
    target = document.getElementById(params[0]);
    if(target)
        target.parentNode.removeChild(target);
}
