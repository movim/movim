var RoomsUtils = {
    morePictures(button, room, page) {
        button.remove();
        RoomsUtils_ajaxHttpGetPictures(room, page);
    },
    moreLinks(button, room, page) {
        button.remove();
        RoomsUtils_ajaxHttpGetLinks(room, page);
    }
}