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
