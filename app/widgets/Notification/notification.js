/*function removeDiff(id, html, id2) {
    target = document.getElementById(id);
    
    if(target) {
        target.innerHTML = html;*/
        /*
        target.insertAdjacentHTML('beforeend', html);

        var nodes = target.childNodes;

        for(i = 0; i < nodes.length; i++) {
            var n = nodes[i];
            n.onclick = function() {
                this.parentNode.removeChild(this);
            };
            setTimeout(function() {
                if(n.parentNode) n.parentNode.removeChild(n);
                },
                6000);
        }*/
/*    }
    
    setTimeout(function() {
        target = document.getElementById(id);
        target.innerHTML = '';
        },
        3000);
}*/

var DesktopNotification = Notification;

var Notification = {
    refresh : function(keys) {
        var counters = document.querySelectorAll('.counter');
        for(i = 0; i < counters.length; i++) {
            var n = counters[i];
            if(n.dataset.key != null
            && keys[n.dataset.key] != null) {
                n.innerHTML = keys[n.dataset.key];
            }
        }
    },
    counter : function(key, counter) {
        var counters = document.querySelectorAll('.counter');
        for(i = 0; i < counters.length; i++) {
            var n = counters[i];
            if(n.dataset.key != null
            && n.dataset.key == key) {
                //setTimeout(function() {
                    n.innerHTML = counter;
                //}, 2000);
            }
        }
    },
    toast : function(html) {
        target = document.getElementById('toast');
        
        if(target) {
            target.innerHTML = html;
        }
        
        setTimeout(function() {
            target = document.getElementById('toast');
            target.innerHTML = '';
            },
            3000);
    },
    snackbar : function(html, time) {
        target = document.getElementById('snackbar');
        
        if(target) {
            target.innerHTML = html;
        }
        
        setTimeout(function() {
            target = document.getElementById('snackbar');
            target.innerHTML = '';
            },
            time*1000);
    },
    desktop : function(title, body, picture) {
        var notification = new DesktopNotification(title, { icon: picture, body: body });
    }
}

MovimWebsocket.attach(function() {
    Notification_ajaxGet();
});

/**
 * Set a global var for widgets to see if document is focused
 */
var document_focus = true;
var document_title = document.title;
var messages_cpt = 0;
var posts_cpt = 0;
document.onblur = function() { document_focus = false; }
document.onfocus = function() { document_focus = true; messages_cpt = 0; movim_show_cpt(); }

function movim_show_cpt() {
    if(messages_cpt == 0 && posts_cpt == 0)
        document.title = document_title;
    else
        document.title = '(' + messages_cpt + '/' + posts_cpt + ') ' + document_title;
}

/**
 * @brief Increment the counter of the title
 */
function movim_title_inc() {
	messages_cpt++;
	movim_show_cpt();
}

function movim_posts_unread(cpt) {
    posts_cpt = cpt;
    movim_show_cpt();
}

function movim_desktop_notification(title, body, image) {
    var notification = new Notification(title, { icon: image, body: body });
    //notification.onshow = function() { setTimeout(this.cancel(), 15000); }
}

/*
window.addEventListener('load', function () {
  Notification.requestPermission(function (status) {
    // This allows to use Notification.permission with Chrome/Safari
    if (Notification.permission !== status) {
      Notification.permission = status;
    }
  });
});
*/
