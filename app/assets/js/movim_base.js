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

/**
 * TODO : remove this function
 */
function movim_change_class(element, classname, title) {
    var node = document.getElementById(element);
    var tmp;
    for (var i = 0; i < node.childNodes.length; i++) {
        tmp=node.childNodes[i];
        tmpClass = tmp.className;
        if (typeof tmpClass != "undefined" && tmp.className.match(/.*protect.*/)) {
            privacy = node.childNodes[i];
            break;
        }
    }      

    privacy.className = classname;
    privacy.title = title;
}

/**
 * Geolocalisation function
 * TODO : remove this function
 */

function setPosition(node) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition( 
            function (position) {
                var poss = position.coords.latitude +','+position.coords.longitude;
                node.value = poss;
            }, 
            // next function is the error callback
            function (error) { }
            );
    }
}
