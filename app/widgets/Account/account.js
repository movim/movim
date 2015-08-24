var Account = {
    resetPassword : function() {
        var form = document.querySelector('form[name=password]');
        form.reset();
        form.querySelector('a.button').className = 'button color oppose';
    },
    clearAccount : function() {
        var username = localStorage.getItem("username").replace("@", "at");
        localStorage.removeItem(username + '_Init');
        Presence_ajaxLogout();
    }
}

MovimWebsocket.attach(function() {
    Notification.current('account');
});
