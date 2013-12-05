var DtlsSrtpKeyAgreement = {
   DtlsSrtpKeyAgreement: true
};

var optional = {
   optional: [DtlsSrtpKeyAgreement]
};

// Set up audio and video regardless of what devices are present.
var sdpConstraints = {'mandatory': {
                      'OfferToReceiveAudio': true,
                      'OfferToReceiveVideo': true }};

function onIceCandidate(event) {
    /*if (event.candidate) {
    sendMessage({type: 'candidate',
                 label: event.candidate.sdpMLineIndex,
                 id: event.candidate.sdpMid,
                 candidate: event.candidate.candidate});
    noteIceCandidate("Local", iceCandidateType(event.candidate.candidate));
    } else {
    Visio.log('End of candidates.');
    }*/
    Visio.log('onIceCandidate');
    Visio.log(event);
}

function onIceConnectionStateChanged(event) {
    Visio.log('onIceConnectionStateChanged');
    Visio.log(event);
}

function onSignalingStateChanged(event) {
    Visio.log('onSignalingStateChanged');
    Visio.log(event);
}

function onError(err) {
  window.alert(err.message);
}

function onOfferCreated(description) {
    Visio.log(description);
    offer = description;
    pc.setLocalDescription(offer,onSetSessionDescriptionSuccess, onSetSessionDescriptionError);

    sendMessage(offer);
}

function sendMessage(offer) {
  var msgString = JSON.stringify(offer);
  //Visio.log('C->S: ' + msgString);
  Visio.log('Send the proposal.');
  Visio.call(['VisioExt_ajaxSendProposal', msgString]);
}

function onSetSessionDescriptionSuccess() {
  Visio.log('Set session description success.');
}

function onSetSessionDescriptionError(error) {
  Visio.log('Failed to set session description: ' + error.toString());
}

function init() {
    var configuration = {"iceServers":[{"url": "stun:23.21.150.121:3478"}]};

    try {
        pc = new RTCPeerConnection(configuration, optional);

        pc.onicecandidate = onIceCandidate;
        pc.onsignalingstatechange = onSignalingStateChanged;
        pc.oniceconnectionstatechange = onIceConnectionStateChanged;
    } catch (e) {
        Visio.log('Failed to create PeerConnection, exception: ' + e.message);
        alert('Cannot create RTCPeerConnection object; \
              WebRTC is not supported by this browser.');
        return;
    }
    
    if(getUserMedia) {
        if (getUserMedia) {
            getUserMedia = getUserMedia.bind(navigator);
        }
        
        // Request the camera.
        getUserMedia(
        // Constraints
        {
          video: true, audio: true
        },

        // Success Callback
        function(localMediaStream) {
            // Get a reference to the video element on the page.
            var vid = document.getElementById('camera-stream');

            // Create an object URL for the video stream and use this 
            // to set the video source.
            vid.src = window.URL.createObjectURL(localMediaStream);
            
            Visio.log(localMediaStream);
            
            pc.addStream(localMediaStream);
            channel = pc.createDataChannel("visio");
            pc.createOffer(onOfferCreated, onError);
        },

        // Error Callback
        function(err) {
          // Log the error to the console.
          Visio.log('The following error occurred when trying to use getUserMedia: ' + err);
        }


        );
    } else {
        alert('Sorry, your browser does not support getUserMedia');
    }

	
    
    //channel = pc.createDataChannel("visio");
    Visio.log(pc);
    //Visio.log(channel);
}

init();
