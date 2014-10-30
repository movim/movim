function removeDiff(id, html, id2) {    
    var wrapper= document.createElement('div');
    wrapper.innerHTML = html;
    var nodes = wrapper.childNodes;

    target = document.getElementById(id);
    if(target) {
        for(i = 0; i < nodes.length; i++) {
            var n = nodes[i];

            // The notification is already here ?
            if(document.getElementById(id2) == null) {
                target.appendChild(n);
                setTimeout(function() {
                    n.parentNode.removeChild(n);
                    },
                    6000);
            }
        }
    }
}
