/**
 * @brief Definition of the MovimMap object
 */
var MovimMap = {
    init: function() {
        if(document.getElementById('postsmap') == null) return;
         
        MovimMap.postsmap = L.map('postsmap').setView([40,0], 2);
        
        L.tileLayer("http://tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "Map data &copy; <a href=\"http://openstreetmap.org\">OpenStreetMap</a> contributors, <a href=\"http://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>, Mapnik ©",
            maxZoom: 18
        }).addTo(MovimMap.postsmap);

        MovimMap.bound = [];

        MovimMap.layerGroup = new L.LayerGroup().addTo(MovimMap.postsmap);
    },
    refresh: function() {
        if(document.getElementById('postsmap') == null) return;
        
        if(MovimMap.postsmap != null) {
            MovimMap.layerGroup.clearLayers();
        }
        
        var articles = document.querySelectorAll('article');
        for(var i = 0; i < articles.length; i++) {
            var article = articles[i];
            if(article.dataset.lat != null) {
                MovimMap.addMarker(article.dataset.lat, article.dataset.lon, article.id);
            }
        }

        MovimMap.addContact();

        MovimMap.fit();
    },
    addContact: function() {
        if(document.getElementById('postsmap') == null) return;
        
        var profile = document.querySelector('#contactsummary_widget .profile');

        if(profile.dataset != null && profile.dataset.lat != null) {
            var popup = "<img style='float: left; margin-right: 1em;' src='" + profile.dataset.avatar + "'/>" +
                        "<div style='padding: 0.5em;'>" + profile.dataset.date + '</div>';
            
            var red = L.icon({
                iconUrl: BASE_URI + '/themes/movim/img/marker-icon.png',
                iconSize:     [25,41], // size of the icon
                shadowSize:   [50, 64], // size of the shadow
                iconAnchor:   [13, 41]
            });

            var marker = L.marker([profile.dataset.lat ,profile.dataset.lon], {icon: red}).addTo(MovimMap.postsmap);
            marker.bindPopup(popup).openPopup();

            MovimMap.bound.push([profile.dataset.lat, profile.dataset.lon]);

            MovimMap.fit();
        }
    },
    addMarker: function(lat, lon, id) {
        var marker = L.marker([lat, lon]);
        MovimMap.layerGroup.addLayer(marker);
        
        if(id != null) marker.onclick = function() { document.location = '#' + id};

        MovimMap.bound.push([lat, lon]);
    },
    fit: function() {
        MovimMap.postsmap.fitBounds(MovimMap.bound);
    },
}
