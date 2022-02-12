var RoomsUtils = {
    getDrawerFingerprints: function (room) {
        var store = new ChatOmemoStorage();
        store.getLocalRegistrationId().then(deviceId => {
            RoomsUtils_ajaxGetDrawerFingerprints(room, deviceId);
        });
    },
    morePictures(button, room, page) {
        button.remove();
        RoomsUtils_ajaxHttpGetPictures(room, page);
    },
    moreLinks(button, room, page) {
        button.remove();
        RoomsUtils_ajaxHttpGetLinks(room, page);
    },
    configureCreatedRoom() {
        RoomsUtils_ajaxConfigureCreated(MovimUtils.formToJson('bookmarkmucadd'));
    },
    configureDisconnect(room) {
        setTimeout(e => {
            Rooms_ajaxExit(room);
        }, 2000)
    },
    resolveRoomEncryptionState(room) {
        var store = new ChatOmemoStorage();
        store.getContactState(room).then(enabled => {
            if (!enabled) {
                document.querySelector('#room_omemo_fingerprints ul.list').classList.add('disabled');
            }
        });
    }
}