var Tabs = {
    create : function() {
        // We search all the div with "tab" class
        var tabs = document.querySelectorAll('.tabelem');

        if (tabs.length == 0) return;

        var current = null;

        document.querySelector('#navtabs').innerHTML = '';

        // We create the list
        for (var i=0; i<tabs.length; i++) {
            if (window.location.hash == '#' + tabs[i].id + '_tab') {
                current = tabs[i].id;
            }

            var li = document.createElement('li');
            li.setAttribute('class', tabs[i].id);
            li.onclick = function() { Tabs.change(this) };
            li.setAttribute('title', tabs[i].title);

            var a = document.createElement('a');
            a.href = '#';
            a.onclick = function(e) {
                e.preventDefault();
                return false;
            };

            if (tabs[i].dataset.mobileicon) {
                aMobile = a.cloneNode(true);
                a.classList.add('on_desktop');
                aMobile.classList.add('on_mobile');

                var icon = document.createElement('i');
                icon.classList.add('material-icons');
                icon.innerText = tabs[i].dataset.mobileicon;
                aMobile.appendChild(icon);

                li.appendChild(aMobile);
            }

            a.appendChild(document.createTextNode(tabs[i].title));
            li.appendChild(a);

            document.querySelector('#navtabs').appendChild(li);
        }

        // We show the first tab
        tabs[0].classList.remove('hide');

        if (tabs.length == 3) {
            document.querySelector('#navtabs').classList.add('wide');
        }

        if (current != null) {
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
            if (hash) {
                Tabs.change(document.querySelector('li.' + hash));
            }
        }
    },

    change : function(current) {
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
        for (var i = 0; i < tabs.length; i++) {
            tabs[i].classList.add('hide');
        }

        // We show the selected div
        var tabOn = document.getElementById(selected);
        tabOn.classList.remove('hide');

        window.history.pushState(null, null, '#' + selected + '_tab');

        // We try to call ajaxDisplay
        if (typeof window[tabOn.title + '_ajaxDisplay'] == 'function') {
            window[tabOn.title + '_ajaxDisplay'].apply();
        }

        // We reset the scroll
        document.querySelector('#navtabs').parentNode.scrollTop = 0;
    }
};

document.addEventListener("DOMContentLoaded", function(event) {
    Tabs.create();
});
