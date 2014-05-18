/*
 * Movim Lazy
 * Implement simple tools to do a lazy page loading by updating only parts of it
 */
 
function MovimLazy() 
{
    this.init = function() 
    {
        var links = document.querySelectorAll('ul.menu a:not([href^=http])');
        for(var i = 0; i < links.length; i++) {
            if(links[i].search != null) {
                links[i].onclick = function(event) {
                    event.preventDefault();
                    var next = this.search.split('&')[0].substring(3);
                    movim_ajaxSend('lazy', 'get', [CURRENT_PAGE, next]);
                };
            }
        }
    };
}

//var lazy = new MovimLazy();
//movim_add_onload(function() { lazy.init() });
