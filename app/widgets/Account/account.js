var Account = {
    resetPassword : function() {
        var form = document.querySelector('form[name=password]');
        form.reset();
        form.querySelector('a.button').className = 'button color oppose';
    }
}

MovimWebsocket.attach(function() {
    Notification.current('account');
});
