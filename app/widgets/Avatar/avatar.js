var Avatar = {
    file : function(files) {
        var f = files[0];
        if (!f.type.match(/image.*/)) {
          console.log("Not a picture !");
        } else {
            var reader = new FileReader();
            reader.readAsDataURL(f);

            reader.onload = function (ev) {
                MovimUtils.getOrientation(f, function(orientation) {
                    Avatar.preview(ev.target.result, orientation);
                });
            };
        };
    },
    preview : function(src, orientation) {
        var canvas = document.createElement('canvas');
        width = height = canvas.width = canvas.height = 350;

        var image = new Image();
        image.src = src;
        image.onload = function(){
            ctx = canvas.getContext("2d");

            switch (orientation) {
                case 2: ctx.transform(-1, 0, 0, 1, width, 0); break;
                case 3: ctx.transform(-1, 0, 0, -1, width, height ); break;
                case 4: ctx.transform(1, 0, 0, -1, 0, height ); break;
                case 5: ctx.transform(0, 1, 1, 0, 0, 0); break;
                case 6: ctx.transform(0, 1, -1, 0, height , 0); break;
                case 7: ctx.transform(0, -1, -1, 0, height , width); break;
                case 8: ctx.transform(0, -1, 1, 0, 0, width); break;
                default: ctx.transform(1, 0, 0, 1, 0, 0);
            }

            if (image.width == image.height) {
                ctx.drawImage(image, 0, 0, width, height);
            } else {
                minVal = parseInt(Math.min(image.width, image.height));
                if (image.width > image.height) {
                    ctx.drawImage(image, (parseInt(image.width) - minVal) / 2, 0, minVal, minVal, 0, 0, width, height);
                } else {
                    ctx.drawImage(image, 0, (parseInt(image.height) - minVal) / 2, minVal, minVal, 0, 0, width, height);
                }
            }

            var base64 = canvas.toDataURL('image/jpeg', 0.95);

            var preview = document.querySelector('form[name=avatarform] img');

            var list = document.querySelector('form[name=avatarform] ul');
            if(list) MovimUtils.hideElement(list);

            var input = document.querySelector('input[name="photobin"]');

            if(preview.className == 'error') preview.className = '';

            preview.src = base64;

            var bin = base64.split(",");
            input.value = bin[1];
        }
    }
}

MovimWebsocket.attach(function() {
    Avatar_ajaxGetAvatar();
});

/*
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
    }
}*/
