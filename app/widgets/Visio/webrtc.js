var DtlsSrtpKeyAgreement = {
   DtlsSrtpKeyAgreement: true
};

var optional = {
   optional: [DtlsSrtpKeyAgreement]
};

var pc;
var remoteStream;
var localStream;

// The RTCPeerConnection configuration
var configuration = {"iceServers":[{"url": "stun:stun.services.mozilla.com"}]};

// Set up audio and video regardless of what devices are present.
var sdpConstraints = {'mandatory': {
                      'OfferToReceiveAudio': true,
                      'OfferToReceiveVideo': true }};

function onIceConnectionStateChanged(event) {
    Visio.log('onIceConnectionStateChanged');
    Visio.log(event);
}

function onSignalingStateChanged(event) {
    Visio.log('onSignalingStateChanged');
    Visio.log(event);
}

function onIceCandidateAdded(event) {
    Visio.log('onIceCandidateAdded');
    Visio.log(event);
}

function onRemoteIceCandidateAdded(event) {
    Visio.log('onRemoteIceCandidateAdded');
    Visio.log(event);
}

function onRemoteIceCandidateError(event) {
    Visio.log('onRemoteIceCandidateError');
    Visio.log(event);
}

function onSignalingStateChanged(event) {
    Visio.log('onSignalingStateChanged');
    Visio.log(event);
}

function onRemoteStreamAdded(event) {
    var vid = document.getElementById('remote-video');
    
    vid.src = window.URL.createObjectURL(event.stream);
    
    remoteStream = event.stream;

    //console.log(remoteStream);
    //console.log(vid);
    /*
    audioTracks = remoteStream.getAudioTracks();

    for (i = 0; i < audioTracks.length; i++) {
      audioTracks[i].enabled = true;
    }*/
}

function onError(err) {
    console.log(err);
}

function onOfferCreated(offer) {
    pc.setLocalDescription(offer,onSetSessionDescriptionSuccess, onSetSessionDescriptionError);

    sendMessage(offer);
}

function onAnswerCreated(offer) {
    pc.setLocalDescription(offer,onSetSessionDescriptionSuccess, onSetSessionDescriptionError);

    sendMessage(offer, true);    
}

function onIceCandidate(event) {
    Visio.log('onIceCandidate');

    console.log('CANDIDATE');
    console.log(event);
    candidate = {};

    if(event.candidate != null) {
        candidate.sdp = event.candidate.candidate;
        candidate.mid = event.candidate.sdpMid;
        candidate.line = event.candidate.sdpMLineIndex;

        candidate.jid = VISIO_JID;
        candidate.resource = VISIO_RESOURCE;

        var msgString = JSON.stringify(candidate);
        
        Visio_ajaxSendCandidate(msgString);
    }

}

function sendTerminate(reason) {
    Visio_ajaxSendSessionTerminate(VISIO_JID, VISIO_RESOURCE, reason);
}

function sendMessage(msg, accept) {
    offer = {};
    offer.sdp = msg.sdp;
    
    offer.jid = VISIO_JID;
    offer.resource = VISIO_RESOURCE;

    document.getElementById('visio').className = 'calling';
        
    if(webrtcDetectedBrowser == 'chrome') {
        setTimeout(function() {
            if(!accept)
                offer.sdp = pc.localDescription.sdp;
        
            var msgString = JSON.stringify(offer);
            
            if(accept) {
                Visio.log('Send the acceptance.');
                Visio.log('ACCEPTANCE ' + msg.sdp);
                Visio_ajaxSendAcceptance(msgString);
            } else {
                Visio.log('Send the proposal.');
                Visio.log('PROPOSAL ' + msg.sdp);

                console.log(msg.sdp);

                Visio_ajaxSendProposal(msgString);      
            }
        }, 1000);
    } else {
        var msgString = JSON.stringify(offer);

        if(accept) {
            Visio.log('Send the acceptance.');
            Visio.log('ACCEPTANCE ' + msg.sdp);
            Visio_ajaxSendAcceptance(msgString);
        } else {
            Visio.log('Send the proposal.');
            Visio.log('PROPOSAL ' + msg.sdp);
            
            Visio_ajaxSendProposal(msgString);      
        }
    }
}

function onSetSessionDescriptionSuccess() {
    Visio.log('Set local session description success.');
}

function onSetSessionDescriptionError(error) {
    Visio.log('Failed to set local session description: ' + error.toString());
    sendTerminate('failed-application');
}

function onSetRemoteSessionDescriptionSuccess() {
    Visio.log('Set remote session description success.');
}

function onSetRemoteSessionDescriptionError(error) {
    Visio.log('Failed to set remote session description: ' + error.message);
    sendTerminate('failed-application');
}

function onOffer(offer) {
    console.log(offer);
    Visio.log('Offer received.');
    Visio.log('OFFER ' + offer);

    init(false);
    
    if(offer != null) {
        
        var message = {};
        message.sdp = offer+"\n";
        message.type = 'offer';
        
        var desc = new RTCSessionDescription(message);
        
        pc.setRemoteDescription(desc,
            onSetRemoteSessionDescriptionSuccess, onSetRemoteSessionDescriptionError);  
        answer(false);
    }
}

function onAccept(offer) {  
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

function onCandidate(sdp, media) {
    var label = {
            'audio' : 0,
            'video' : 1
        };
    
    var candidate = new RTCIceCandidate({sdpMLineIndex: label[media],
                                         candidate: sdp});

    pc.addIceCandidate(candidate, onRemoteIceCandidateAdded, onRemoteIceCandidateError);
}

function preInit(isCaller) {
    // We try to grab TURN servers, init() is called here
    maybeRequestTurn(isCaller);
}

function init(isCaller) {
    try {
        console.log(configuration);
        pc = new RTCPeerConnection(configuration, optional);

        console.log('Created RTCPeerConnnection with:\n' +
                    '  config: \'' + JSON.stringify(configuration) + '\';\n' +
                    '  constraints: \'' + JSON.stringify(optional) + '\'.');

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
}

function answer(isCaller) {
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

            localStream = localMediaStream;

            pc.addStream(localStream);
            channel = pc.createDataChannel("visio");
            
            if(isCaller)
                pc.createOffer(onOfferCreated, onError);
            else
                pc.createAnswer(onAnswerCreated, onError);
        },

        // Error Callback
        function(err) {
            // Log the error to the console.
            Visio.log('The following error occurred when trying to use getUserMedia: ' + err);
            sendTerminate('decline');
        }


        );
    } else {
        alert('Sorry, your browser does not support getUserMedia');
    }
}

function terminate() {
    // We close the RTCPeerConnection
    if(pc != null && pc.signalingState != 'closed')
        pc.close();

    // We close the local webcam and microphone
    if(localStream != null)
        localStream.stop();
    remoteStream = null;
    
    // Get a reference to the video elements on the page.
    var vid = document.getElementById('local-video');
    var rvid = document.getElementById('remote-video');

    document.getElementById('visio').className = '';
}
