var Location = {
    init : function() {
        if(navigator.geolocation) {
            let locationIcon = document.querySelector('#location_widget span.primary i');
            locationIcon.innerHTML = 'location_searching';
            locationIcon.parentElement.classList.add('searching');

            let locationToggleIcon = document.querySelector('#location_toggle .placeholder i');
            if (locationToggleIcon) {
                locationToggleIcon.innerHTML = 'location_searching';
                locationToggleIcon.parentElement.classList.add('searching');
            }

            navigator.geolocation.getCurrentPosition(Location.geoSuccess, Location.geoError);
        }
    },

    geoSuccess : function (location) {
        let locationIcon = document.querySelector('#location_widget span.primary i');
        locationIcon.innerHTML = 'my_location';
        locationIcon.parentElement.classList.remove('searching');

        let locationToggleIcon = document.querySelector('#location_toggle .placeholder i');
        if (locationToggleIcon) {
            locationToggleIcon.innerHTML = 'my_location';
            locationToggleIcon.parentElement.classList.remove('searching');
        }

        Location_ajaxPublish(
            location.coords.latitude,
            location.coords.longitude,
            location.coords.accuracy
        );
    },

    geoError : function (error) {

    }
}

MovimWebsocket.initiate(() => Location_ajaxHttpGet());
