MovimWebsocket.attach(function() {
    Avatar_ajaxGetAvatar();
});

/*
function showVideo() {
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

function errorCallback(error) {
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
    }
}*/
