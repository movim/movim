var DtlsSrtpKeyAgreement = {
   DtlsSrtpKeyAgreement: true
};

var optional = {
   optional: [DtlsSrtpKeyAgreement]
};

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

function onError(err) {
  window.alert(err.message);
}

function onOfferCreated(description) {
  offer = description;
  pc.setLocalDescription(offer, onPc1LocalDescriptionSet, onError);
}

function onPc1LocalDescriptionSet() {
  // after this function returns, pc1 will start firing icecandidate events
  //pc2.setRemoteDescription(offer, onPc2RemoteDescriptionSet, onError);
}

function init() {
    var configuration = {"iceServers":[{"url": "stun:23.21.150.121:3478"}]};

    try {
        pc = new RTCPeerConnection(configuration, optional);

        pc.onicecandidate = onIceCandidate;
        pc.onsignalingstatechange = onSignalingStateChanged;
        pc.oniceconnectionstatechange = onIceConnectionStateChanged;
        
        pc.createOffer(onOfferCreated, onError);
    } catch (e) {
        console.log('Failed to create PeerConnection, exception: ' + e.message);
        alert('Cannot create RTCPeerConnection object; \
              WebRTC is not supported by this browser.');
        return;
    }
    
    if(getUserMedia) {
        /*if (getUserMedia) {
            getUserMedia = getUserMedia.bind(navigator);
        }*/
        
        // Request the camera.
        getUserMedia(
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
