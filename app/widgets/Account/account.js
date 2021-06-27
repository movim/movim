var Account = {
    resetPassword : function() {
        var form = document.querySelector('form[name=password]');
        form.reset();
        document.querySelector('#password_save').className = 'button color flat';
    }
}

MovimWebsocket.attach(function() {
    let omemoStorage = new ChatOmemoStorage;
    omemoStorage.getIdentityKeyPair().then(keyPair => {
        Account_ajaxHttpGetFingerprints(MovimUtils.arrayBufferToBase64(keyPair.pubKey));
    }).catch(a => {
        Account_ajaxHttpGetFingerprints();
    });

    Notification.current('account');
});
