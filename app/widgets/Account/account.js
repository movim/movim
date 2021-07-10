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
        Account_ajaxHttpGetFingerprints(MovimUtils.arrayBufferToBase64(keyPair.pubKey), omemoStorage.getSessionsIds(USER_JID));
    }).catch(a => {
        Account_ajaxHttpGetFingerprints(null, store.getSessionsIds(USER_JID));
    });

    Notification.current('account');
});
