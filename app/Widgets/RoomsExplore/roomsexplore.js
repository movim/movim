var RoomsExplore = {
    timer : null,

    init : function() {
        if (window.matchMedia("(min-width: 1025px)").matches) {
            document.querySelector('#roomsexplore_bar input[name=keyword]').focus();
        }
    },

    searchSomething : function(value) {
        clearTimeout(RoomsExplore.timer);

        if (value !== '') {
            document.querySelector('#roomsexplore_bar li.search').classList.add('searching');
        }

        RoomsExplore.timer = setTimeout(() => {
            RoomsExplore_ajaxSearchRooms(value);
        }, 700);
    },

    searchClear : function() {
        document.querySelector('#roomsexplore_bar li.search').classList.remove('searching');
    }
}
