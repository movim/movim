var ContactActions = {
    getDrawerFingerprints : function(jid) {
        var store = new ChatOmemoStorage();
        store.getLocalRegistrationId().then(deviceId => {
            ContactActions_ajaxGetDrawerFingerprints(jid, deviceId);
        });
    },
    morePictures(button, jid, page) {
        button.remove();
        ContactActions_ajaxHttpGetPictures(jid, page);
    },
    moreLinks(button, jid, page) {
        button.remove();
        ContactActions_ajaxHttpGetLinks(jid, page);
    },
    resolveSessionsStates : function(jid) {
        var store = new ChatOmemoStorage();

        store.getSessionsIds(jid).map(id => {
            store.getSessionState(jid + '.' + id).then(state => {
                if (state) {
                    let icon = document.querySelector('span#sessionicon_' + id);
                    icon.classList.remove('blue');
                    icon.classList.add('blue');
                    let checkbox = document.querySelector('input[name=sessionstate_' + id + ']');
                    checkbox.checked = true;
                }
            })
        });

        store.getContactState(jid).then(enabled => {
            if (!enabled) {
                document.querySelector('#omemo_fingerprints ul.list').classList.add('disabled');
            }
        });
    },
    toggleFingerprintState : function(checkbox) {
        var store = new ChatOmemoStorage();
        store.setSessionState(checkbox.dataset.identifier, checkbox.checked);
    }
}