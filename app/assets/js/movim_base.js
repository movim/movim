/**
 * Movim Base
 *
 * Some basic functions essential for Movim
 */

var onloaders = new Array();

/**
 * @brief Adds a function to the onload event
 * @param function func
 */
function movim_add_onload(func)
{
    onloaders.push(func);
}

/**
 * @brief Function that is run once the page is loaded.
 */
function movim_onload()
{
    for(var i = 0; i < onloaders.length; i++) {
        if(typeof(onloaders[i]) === "function")
            onloaders[i]();
    }
}

window.onload = function() { movim_onload(); };
