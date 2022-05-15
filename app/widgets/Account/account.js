var Account = {
    resetPassword : function() {
        var form = document.querySelector('form[name=password]');
        form.reset();
        document.querySelector('#password_save').className = 'button color flat';
    },
    resolveSessionsStates : function() {
        var store = new ChatOmemoStorage();
        store.getSessionsIds(store.jid).map(id => {
            store.getSessionState(store.jid + '.' + id).then(state => {
                if (state) {
                    let checkbox = document.querySelector('input[name=accountsessionstate_' + id + ']');

                    if (checkbox) {
                        checkbox.checked = true;
                    }
                }
            })
        });
    },
    toggleFingerprintState : function(checkbox) {
        var store = new ChatOmemoStorage();
        store.setSessionState(checkbox.dataset.identifier, checkbox.checked);
    },
    refreshFingerprints : function() {
        let omemoStorage = new ChatOmemoStorage;

        omemoStorage.getIdentityKeyPair().then(keyPair => {
            Account_ajaxHttpGetFingerprints(MovimUtils.arrayBufferToBase64(keyPair.pubKey), omemoStorage.getSessionsIds(USER_JID));
        }).catch(a => {
            Account_ajaxHttpGetFingerprints(null, store.getSessionsIds(USER_JID));
        });
    }
}

MovimWebsocket.attach(function() {
    Account.refreshFingerprints();
    Account_ajaxHttpGetPresences();

    Notification.current('account');
});
