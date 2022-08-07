var Toast = {
    send : function(title, timeout) {
        target = document.getElementById('toast');

        if (target) {
            target.innerHTML = title;
        }

        setTimeout(function() {
            target = document.getElementById('toast');
            target.innerHTML = '';
        }, timeout ?? 3000);
    }
}