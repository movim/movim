var Account = {
    resetPassword: function () {
        var form = document.querySelector('form[name=password]');
        form.reset();
        document.querySelector('#password_save').className = 'button color flat';
    },
    resolveSessionsStates: function () {
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
    toggleFingerprintState: function (checkbox) {
        var store = new ChatOmemoStorage();
        store.setSessionState(checkbox.dataset.identifier, checkbox.checked);
    },
    deleteBundle: async function (bundleid) {
        ChatOmemo.resolveContactFingerprints(USER_JID).then(remoteKeys => {
            remoteKeys = remoteKeys.filter(remoteKey =>
                remoteKey.bundleid == bundleid
            );

            Account_ajaxDeleteBundle(remoteKeys[0])
        });
    },
    deleteBundleConfirm: function (bundleid) {
        var store = new ChatOmemoStorage();

        var address = new libsignal.SignalProtocolAddress(USER_JID, bundleid);
        store.removeSession(address);

        Account_ajaxDeleteBundleConfirm(bundleid, store.getOwnSessionsIds());
    },
    refreshFingerprints: async function () {
        ChatOmemo.resolveContactFingerprints(USER_JID).then(remoteKeys => {
            let omemoStorage = new ChatOmemoStorage;

            omemoStorage.getLocalRegistrationId().then(localBundleId => {
                omemoStorage.getIdentityKeyPair().then(identityKey => {
                    // Remove it if its already there
                    remoteKeys = remoteKeys.filter(remoteKey =>
                        remoteKey.bundleid != localBundleId.toString()
                    );

                    remoteKeys.unshift({
                        jid: USER_JID,
                        self: true,
                        bundleid: localBundleId.toString(),
                        fingerprint: MovimUtils.arrayBufferToBase64(identityKey.pubKey)
                    });

                    Account_ajaxHttpGetFingerprints(remoteKeys);
                });
            });
        });
    }
}

MovimWebsocket.attach(function () {
    if (OMEMO_ENABLED) Account.refreshFingerprints();
    Account_ajaxHttpGetPresences();
    Account_ajaxGetGateways();
});
