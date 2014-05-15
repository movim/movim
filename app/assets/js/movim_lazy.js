/*
 * Movim Lazy
 * Implement simple tools to do a lazy page loading by updating only parts of it
 */
 
function MovimLazy() 
{
    this.init = function() 
    {
        var links = document.querySelectorAll('a:not([href^=http])');
        for(var i = 0; i < links.length; i++) {
            if(links[i].getAttribute('href') != null) {
                var next = links[i].getAttribute('href').split('&')[0].substring(3);
                var current = 
                links[i].onclick = function(event) {
                    event.preventDefault();
                    movim_ajaxSend('lazy', 'get', [CURRENT_PAGE, next]);
                };
            }
        }
    };
}

var lazy = new MovimLazy();
movim_add_onload(function() { lazy.init() });
