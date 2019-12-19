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
            document.querySelector('#roomsexplore_bar span.primary i').innerText = 'autorenew';
            document.querySelector('#roomsexplore_bar span.primary').classList.add('spin');
        }

        RoomsExplore.timer = setTimeout(() => {
            RoomsExplore_ajaxSearchRooms(value);
        }, 700);
    },

    searchClear : function() {
        document.querySelector('#roomsexplore_bar span.primary i').innerText = 'search';
        document.querySelector('#roomsexplore_bar span.primary').classList.remove('spin');
    }
}