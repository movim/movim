function getPos(n)
{
    //n.style.display = "none";
	if(navigator.geolocation){
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
    
    canvas.width = canvas.height = 210;
    
    var width = canvas.width;
    var height = canvas.height;
 
    if (img.width == img.height) {
        canvas.getContext("2d").drawImage(img, 0, 0, width, height);
    } else {
        minVal = Math.min(img.width, img.height);
        if (img.width > img.height) {
            canvas.getContext("2d").drawImage(img, (img.width - minVal) / 2, 0, minVal, minVal, 0, 0, width, height);
        } else {
            canvas.getContext("2d").drawImage(img, 0, (img.height - minVal) / 2, minVal, minVal, 0, 0, width, height);
        }
    }

    canvas.style.width = 200;
    canvas.style.height = 200;
    
    var base64 = canvas.toDataURL('image/jpeg', 0.9);
    var bin = base64.split(",");
    document.querySelector('#vCardPhotoPreview').src = base64;
    document.querySelector('input[name="phototype"]').value = 'image/jpeg';
    document.querySelector('input[name="photobin"]').value = bin[1];
    
    function bytesToSize(bytes) {
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        if (bytes == 0) return 'n/a';
        var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
        return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
    };
    
    document.getElementById("picturesize").innerHTML = bytesToSize(encodeURI(base64).split(/%..|./).length - 1);
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

function showVideo(){
	navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia; 
	navigator.getUserMedia({video:true, audio:false}, successCallback, errorCallback);
    
    movim_toggle_class('#camdiv', 'active');
}
function successCallback(stream) {
  video = document.getElementById("runningcam");
  video.src = window.URL.createObjectURL(stream);
  localMediaStream = stream; // stream available to console

  document.getElementById("shoot").addEventListener('click', snapshot, false);
}

function errorCallback(error){
  console.log("navigator.getUserMedia error: ", error);
}


function snapshot() {
	if (localMediaStream) {
		canvas = document.querySelector("canvas");
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
		ctx = canvas.getContext('2d');
		video = document.getElementById("runningcam");
		
		ctx.drawImage(video,0,0, canvas.width, canvas.height);
		// "image/webp" works in Chrome 18. In other browsers, this will fall back to image/png.
		var img = new Image();
            img.src = canvas.toDataURL('image/png');

            img.onload = function() {
                vCardImageResize(this);
            }
		//document.querySelector("#snap").src = canvas.toDataURL('image/webp');
	}
}
