function removeDiff(id, html, id2) {
    target = document.getElementById(id);
    
    if(target) {
        target.innerHTML = html;
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
    }
    
    setTimeout(function() {
        target = document.getElementById(id);
        target.innerHTML = '';
        },
        3000);
}

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
    }
}

MovimWebsocket.attach(function() {
    Notification_ajaxGet();
});

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
