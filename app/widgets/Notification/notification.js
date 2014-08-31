function removeDiff(params) {
    if(params.length < 2) {
        return;
    }
    
    var wrapper= document.createElement('div');
    wrapper.innerHTML = params[1];
    var nodes = wrapper.childNodes;

    target = document.getElementById(params[0]);
    if(target) {
        for(i = 0; i < nodes.length; i++) {
            var n = nodes[i];

            // The notification is already here ?
            if(document.getElementById(params[2]) == null) {
                target.appendChild(n);
                setTimeout(function() {
                    n.parentNode.removeChild(n);
                    },
                    6000);
            }
        }
    }
}
