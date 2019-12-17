var RoomsExplore = {
    timer : null,

    init : function() {
        if (window.matchMedia("(min-width: 1025px)").matches) {
            document.querySelector('#roomsexplore_bar input[name=keyword]').focus();
        }
    },

    searchSomething : function(value) {
        clearTimeout(RoomsExplore.timer);

        RoomsExplore.timer = setTimeout(() => {
            RoomsExplore_ajaxSearchRooms(value);
        }, 700);
    }
}