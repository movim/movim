var DtlsSrtpKeyAgreement = {
   DtlsSrtpKeyAgreement: true
};

var optional = {
   optional: [DtlsSrtpKeyAgreement]
};

var pc;
var remoteStream;

// Set up audio and video regardless of what devices are present.
var sdpConstraints = {'mandatory': {
                      'OfferToReceiveAudio': true,
                      'OfferToReceiveVideo': true }};

function onIceCandidate(event) {
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

function onRemoteStreamAdded(event) {
    var vid = document.getElementById('remote-video');
    
    vid.src = window.URL.createObjectURL(event.stream);
    
    /*remoteStream = event.stream;
    
    audioTracks = remoteStream.getAudioTracks();

    for (i = 0; i < audioTracks.length; i++) {
      audioTracks[i].enabled = true;
    }*/
}

function onError(err) {
    window.alert(err.message);
}

function onOfferCreated(offer) {
    //Visio.log(offer);

    pc.setLocalDescription(offer,onSetSessionDescriptionSuccess, onSetSessionDescriptionError);

    sendMessage(offer);
}

function onAnswerCreated(offer) {
    //Visio.log(offer);

    pc.setLocalDescription(offer,onSetSessionDescriptionSuccess, onSetSessionDescriptionError);

    sendMessage(offer, true);    
}

function sendMessage(msg, accept) {
    offer = {};
    offer.sdp = msg.sdp;
    offer.jid = VISIO_JID;
    offer.ressource = VISIO_RESSOURCE;
    
    var msgString = JSON.stringify(offer);
    
    if(accept) {
        Visio.log('Send the acceptance.');
        Visio.log('ACCEPTANCE ' + msg.sdp);
        Visio.call(['VisioExt_ajaxSendAcceptance', msgString]);
    } else {
        Visio.log('Send the proposal.');
        Visio.log('PROPOSAL ' + msg.sdp);
        Visio.call(['VisioExt_ajaxSendProposal', msgString]);
    }
}

function onSetSessionDescriptionSuccess() {
    Visio.log('Set session description success.');
}

function onSetSessionDescriptionError(error) {
    Visio.log('Failed to set session description: ' + error.toString());
}

function onSetRemoteSessionDescriptionSuccess() {
    Visio.log('Set remote session description success.');
}

function onSetRemoteSessionDescriptionError(error) {
    Visio.log('Failed to set remote session description: ' + error.message);
}

function onOffer(offer) {
    offer = offer[0];
    
    Visio.log('Offer received.');
    Visio.log('OFFER ' + offer);  
      
    if(!pc)
        init(false);
    
    if(offer != null) {
        var desc = new RTCSessionDescription();
        desc.sdp = offer;
        desc.type = 'offer';
        
        pc.setRemoteDescription(desc,
            onSetRemoteSessionDescriptionSuccess, onSetRemoteSessionDescriptionError);  
    }
}

function onAccept(offer) {
    offer = offer[0];
    
    Visio.log('Accept received.');
    Visio.log('ACCEPT ' + offer);  
    
    if(offer != null) {
        var desc = new RTCSessionDescription();
        desc.sdp = offer;
        desc.type = 'answer';
        
        pc.setRemoteDescription(desc,
            onSetRemoteSessionDescriptionSuccess, onSetRemoteSessionDescriptionError);  
    }
}

function init(isCaller) {
    var configuration = {"iceServers":[{"url": "stun:23.21.150.121:3478"}]};

    try {
        pc = new RTCPeerConnection(configuration, optional);

        pc.onicecandidate = onIceCandidate;
        pc.onsignalingstatechange = onSignalingStateChanged;
        pc.oniceconnectionstatechange = onIceConnectionStateChanged;
        pc.onaddstream = onRemoteStreamAdded;
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
            var vid = document.getElementById('local-video');
            var avatar = document.getElementById('avatar');

            // Create an object URL for the video stream and use this 
            // to set the video source.
            vid.src = window.URL.createObjectURL(localMediaStream);
            
            setTimeout(
                function() { 
                    vid.className = 'tiny';
                    avatar.className = 'tiny';
                }, 
                3000);
            
            Visio.log(localMediaStream);
            
            pc.addStream(localMediaStream);
            channel = pc.createDataChannel("visio");
            
            console.log(pc);
            
            if(isCaller)
                pc.createOffer(onOfferCreated, onError);
            else
                pc.createAnswer(onAnswerCreated, onError);
            
            //pc.createOffer(onOfferCreated, onError);
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

    //Visio.log(pc);
}

function terminate() {
    pc.close();
    Visio.call(['VisioExt_ajaxSendSessionTerminate', VISIO_JID, VISIO_RESSOURCE]);
    
    // Get a reference to the video elements on the page.
    var vid = document.getElementById('local-video');
    var rvid = document.getElementById('remote-video');
    var avatar = document.getElementById('avatar');
    vid.className = vid.className.replace('tiny', 'off');
    rvid.className = 'off';
    avatar.className = avatar.className.replace('tiny', '');
}
