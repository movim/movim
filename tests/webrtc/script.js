var movim_getUserMedia         = ( navigator.webkitGetUserMedia     ||
                                   navigator.mozGetUserMedia        ||
                                   navigator.msGetUserMedia         ||
                                   navigator.getUserMedia           );

var movim_RTCPeerConnection    = ( window.webkitRTCPeerConnection   ||
                                   window.mozRTCPeerConnection      ||
                                   window.RTCPeerConnection         );

var WEBRTC_SESSION_DESCRIPTION = ( window.mozRTCSessionDescription  ||
                                   window.RTCSessionDescription     );

var WEBRTC_ICE_CANDIDATE       = ( window.mozRTCIceCandidate        ||
                                   window.RTCIceCandidate           );


function onIceCandidate(event) {
    /*if (event.candidate) {
    sendMessage({type: 'candidate',
                 label: event.candidate.sdpMLineIndex,
                 id: event.candidate.sdpMid,
                 candidate: event.candidate.candidate});
    noteIceCandidate("Local", iceCandidateType(event.candidate.candidate));
    } else {
    console.log('End of candidates.');
    }*/
    console.log('onIceCandidate');
    console.log(event);
}

function onIceConnectionStateChanged(event) {
    console.log('onIceConnectionStateChanged');
    console.log(event);
}

function onSignalingStateChanged(event) {
    console.log('onSignalingStateChanged');
    console.log(event);
}

function init() {
    var configuration = {"iceServers":[{"url": "stun:stun.l.google.com:19302"}]};

    try {
        pc = new movim_RTCPeerConnection(configuration);
        pc.onicecandidate = onIceCandidate;
        pc.onsignalingstatechange = onSignalingStateChanged;
        pc.oniceconnectionstatechange = onIceConnectionStateChanged;
    } catch (e) {
        console.log('Failed to create PeerConnection, exception: ' + e.message);
        alert('Cannot create RTCPeerConnection object; \
              WebRTC is not supported by this browser.');
        return;
    }
    
    if(movim_getUserMedia) {
        if (movim_getUserMedia) {
            movim_getUserMedia = movim_getUserMedia.bind(navigator);
        }
        
        // Request the camera.
        movim_getUserMedia(
        // Constraints
        {
          video: true
        },

        // Success Callback
        function(localMediaStream) {
            // Get a reference to the video element on the page.
            //var vid = document.getElementById('camera-stream');

            // Create an object URL for the video stream and use this 
            // to set the video source.
            //vid.src = window.URL.createObjectURL(localMediaStream);
            pc.addStream(localMediaStream);
        },

        // Error Callback
        function(err) {
          // Log the error to the console.
          console.log('The following error occurred when trying to use getUserMedia: ' + err);
        }


        );
    } else {
        alert('Sorry, your browser does not support getUserMedia');
    }

    
    //channel = pc.createDataChannel("visio");
    console.log(pc);
    //console.log(channel);
}
