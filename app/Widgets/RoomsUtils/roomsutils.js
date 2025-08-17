var RoomsUtils = {
    getDrawerFingerprints: function (room, members) {
        let resolved = [];

        members.forEach(member => {
            resolved.push(ChatOmemo.resolveContactFingerprints(member));
        });

        Promise.all(resolved).then(fingerprints => {
            RoomsUtils_ajaxGetDrawerFingerprints(room, fingerprints);
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
        if (MovimUtils.formToJson('bookmarkmucadd') != false) {
            RoomsUtils_ajaxConfigureCreated(MovimUtils.formToJson('bookmarkmucadd'));
        }
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
