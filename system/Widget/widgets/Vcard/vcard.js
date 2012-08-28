function getPos(n)
{
    //n.style.display = "none";
	if(navigator.geolocation){
        console.log(navigator.geolocation.getCurrentPosition);
	    navigator.geolocation.getCurrentPosition(function(position){
	        var latitude = position.coords.latitude;
	        var longitude = position.coords.longitude;
	        var altitude = position.coords.altitude;
	        //document.getElementById('geolocation').innerHTML = 'latitude : ' + latitude + '<br />' + 'longitude : ' + longitude + '<br />' + 'altitude : ' + altitude + '<br />';
	        document.getElementById('geolocation').innerHTML = '<iframe width="100%" height="250" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://www.openstreetmap.org/?lat='+latitude+'&lon='+longitude+'&zoom=12&marker=1;layers=M"></iframe>';
	        document.forms["vcard"].elements["vCardLat"].value = latitude;
	        document.forms["vcard"].elements["vCardLong"].value = longitude;
	    });
	}
};

function vCardImageResize(img) {
    var canvas = document.createElement('canvas');
    
    var MAX_WIDTH = 200;
    var MAX_HEIGHT = 200;
    var width = img.width;
    var height = img.height;
     
    if (width > height) {
      if (width > MAX_WIDTH) {
        height *= MAX_WIDTH / width;
        width = MAX_WIDTH;
      }
    } else {
      if (height > MAX_HEIGHT) {
        width *= MAX_HEIGHT / height;
        height = MAX_HEIGHT;
      }
    }
    canvas.width = width;
    canvas.height = height;
    var ctx = canvas.getContext("2d");
    ctx.drawImage(img, 0, 0, width, height);
    
    var base64 = canvas.toDataURL('image/jpeg', 0.8);
    var bin = base64.split(",");
    document.querySelector('#vCardPhotoPreview').src = base64;
    document.querySelector('input[name="phototype"]').value = 'image/jpeg';
    document.querySelector('input[name="photobin"]').value = bin[1];
};

function vCardImageLoad(files) {
    var f = files[0];
    if (!f.type.match(/image.*/)) {
      console.log("Not a picture !");
    } else {
        var reader = new FileReader();
        reader.readAsDataURL(f);
        
        reader.onload = function ( ev ) {
            var img = new Image();
            img.src = ev.target.result;
            img.onload = function() {
                vCardImageResize(this);
            };
        };
    };
};
