var ContactActions = {
    getDrawerFingerprints : function(jid) {
        var store = new ChatOmemoStorage();
        store.getLocalRegistrationId().then(deviceId => {
            ContactActions_ajaxGetDrawerFingerprints(jid, deviceId);
        });
    }
}