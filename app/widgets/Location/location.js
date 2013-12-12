var myposition = 0;

function getMyPositionData() { return myposition; }

window.cb = function cb(json) {
    document.getElementById('mapdata').innerHTML = json.display_name + ',' + json.address.city;
    myposition = JSON.stringify(json);
}

function hidePositionChoice()
{
    document.querySelector("#mypossubmit").style.display = 'none';
    document.querySelector("#myposrefuse").style.display = 'none';   
}

function getMyPosition() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition( 
            function (position) {  
                
                document.querySelector("#mapdiv").style.display = 'block';
                
                var map = L.map("mapdiv").setView([position.coords.latitude ,position.coords.longitude], 11);
                
                L.tileLayer("http://tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    attribution: "",
                    maxZoom: 18
                }).addTo(map);
                var marker = L.marker([position.coords.latitude ,position.coords.longitude]).addTo(map)                
                
                var s = document.createElement('script');       
                s.src = 'http://nominatim.openstreetmap.org/reverse?json_callback=cb&format=json&lat='+position.coords.latitude+'&lon='+position.coords.longitude+'&zoom=27&addressdetails=1';
                document.getElementsByTagName('head')[0].appendChild(s);
                
                document.querySelector("#mypossubmit").style.display = 'inline-block';
            }, 
            // next function is the error callback
            function (error)
            {
                switch(error.code) 
                {
                    case error.TIMEOUT:
                        alert ('Timeout');
                        break;
                    case error.POSITION_UNAVAILABLE:
                        alert ('Position unavailable');
                        break;
                    case error.PERMISSION_DENIED:
                        alert ('Permission denied');
                        break;
                    case error.UNKNOWN_ERROR:
                        alert ('Unknown error');
                        break;
                }
            }
            );
    }
    else {
        
    }
}
