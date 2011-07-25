function getPos(n)
{
    n.style.display = "none";
	if(navigator.geolocation){
	    navigator.geolocation.getCurrentPosition(function(position){
	        var latitude = position.coords.latitude;
	        var longitude = position.coords.longitude;
	        var altitude = position.coords.altitude;
	        //document.getElementById('geolocation').innerHTML = 'latitude : ' + latitude + '<br />' + 'longitude : ' + longitude + '<br />' + 'altitude : ' + altitude + '<br />';
	        document.getElementById('geolocation').innerHTML = '<iframe width="100%" height="250" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://labs.metacarta.com/osm/embed.html?lat='+latitude+'&lon='+longitude+'&zoom=12&marker=1"></iframe>';
	        document.forms["vcard"].elements["vCardLat"].value = latitude;
	        document.forms["vcard"].elements["vCardLong"].value = longitude;
	    });
	}
}
//<iframe width="425" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://labs.metacarta.com/osm/embed.html?lat=42&lon=-71&zoom=12&marker=1"></iframe>
