var Toast = {
    send : function(title) {
        // Android notification
        if (typeof Android !== 'undefined') {
            Android.showToast(title);
            return;
        }

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