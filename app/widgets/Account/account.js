var Account = {
    resetPassword : function() {
        var form = document.querySelector('form[name=password]');
        form.reset();
        document.querySelector('#password_save').className = 'button color flat';
    }
}

MovimWebsocket.attach(function() {
    Notification.current('account');
});
