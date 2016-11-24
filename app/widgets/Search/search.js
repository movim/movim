var Search = {
    init : function() {
        document.querySelector('input[name=keyword]').focus();
    },

    roster : function(key) {
        var selector_clear = '#search > #roster > li.found';
        var subheader = document.querySelector('#search > #roster > li.subheader');
        var li = document.querySelectorAll(selector_clear);

        MovimUtils.removeClassInList('found', li);

        if(key == '') return;

        var founds = document.querySelectorAll(
            '#search > #roster > li[name*="' + MovimUtils.cleanupId(key).slice(3) + '"]'
        );

        if(founds.length > 0) {
            subheader.classList.add('found');
            for(i = 0; i < founds.length; i++) {
                MovimUtils.addClass(founds[i], 'found');
                if(i > 4) break;
            }
        } else if(subheader) {
            subheader.classList.remove('found');
        }
    }
}

document.addEventListener('keydown', function(e) {
    if (e.keyCode == 77 && e.ctrlKey) {
        Search_ajaxRequest();
    }
});
