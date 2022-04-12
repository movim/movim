var Toast = {
    send : function(title) {
        target = document.getElementById('toast');

        if (target) {
            target.innerHTML = title;
        }

        setTimeout(function() {
            target = document.getElementById('toast');
            target.innerHTML = '';
        }, 3000);
    }
}