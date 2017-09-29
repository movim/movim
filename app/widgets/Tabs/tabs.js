var Tabs = {
    create : function() {
        // We search all the div with "tab" class
        var tabs = document.querySelectorAll('.tabelem');
        var current = null;

        // We create the list
        var html = '';
        for (var i=0; i<tabs.length; i++){
            if(window.location.hash == '#' + tabs[i].id + '_tab') {
                current = tabs[i].id;
            }

            html += '<li class="' + tabs[i].id + '" onclick="Tabs.change(this);">';
            html += '    <a href="#" onclick="actDifferent(event);">' + tabs[i].title + '</a>';
            html += '</li>';
        }

        // We show the first tab
        MovimUtils.showElement(tabs[0]);

        // We insert the list
        document.querySelector('#navtabs').innerHTML = html;

        if(current != null){
            tab = current;
            menuTab = document.querySelector('li.' + current);
        }

        //if no tab is active, activate the first one
        else {
            tab = document.querySelector('.tabelem').id;
            menuTab = document.querySelector('li.'+ tab);
        }

        Tabs.change(menuTab);

        window.onhashchange = function() {
            var hash = window.location.hash.slice(1, -4);
            if(hash) {
                Tabs.change(document.querySelector('li.' + hash));
            }
        }
    },

    change : function(current){
        // We grab the tabs list
        var navtabs = document.querySelectorAll('#navtabs li');
        // We clean the class of the li
        for (var j = 0; j < navtabs.length; j++) {
            navtabs[j].classList.remove('active');
        }

        // We add the "on" class to the selected li
        var selected = current.className;
        current.classList.add('active');

        // We hide all the div
        var tabs = document.querySelectorAll('.tabelem');
        for (var i = 0; i < tabs.length; i++){
            MovimUtils.hideElement(tabs[i]);
        }

        // We show the selected div
        var tabOn = document.getElementById(selected);
        MovimUtils.showElement(tabOn);

        window.history.pushState(null, null, '#' + selected + '_tab');

        // We try to call ajaxDisplay
        if(typeof window[tabOn.title + '_ajaxDisplay'] == 'function') {
            window[tabOn.title + '_ajaxDisplay'].apply();
        }

        // We reset the scroll
        document.querySelector('#navtabs').parentNode.scrollTop = 0;
    }
};

movim_add_onload(function()
{
    Tabs.create();
});

function actDifferent(e){
    e.preventDefault();
    return false;
}
