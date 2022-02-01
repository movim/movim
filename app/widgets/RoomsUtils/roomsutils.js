var RoomsUtils = {
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
    }
}