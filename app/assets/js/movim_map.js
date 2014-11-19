/**
 * @brief Definition of the MovimMap object
 */
var MovimMap = {
    init: function() {
        MovimMap.postsmap = L.map("postsmap").setView([40,0], 2);
        
        L.tileLayer("http://tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "Map data &copy; <a href=\"http://openstreetmap.org\">OpenStreetMap</a> contributors, <a href=\"http://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>, Mapnik ©",
            maxZoom: 18
        }).addTo(MovimMap.postsmap);

        MovimMap.bound = [];

        MovimMap.layerGroup = new L.LayerGroup().addTo(MovimMap.postsmap);
    },
    refresh: function() {
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

        MovimMap.postsmap.fitBounds(MovimMap.bound);
    },
    addMarker: function(lat, lon, id) {
        var marker = L.marker([lat, lon]);
        MovimMap.layerGroup.addLayer(marker);
        
        if(id != null) marker.onclick = function() { document.location = '#' + id};

        MovimMap.bound.push([lat, lon]);
    },
}
