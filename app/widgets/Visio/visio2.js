function logError(error) {
    console.log(error.name + ': ' + error.message);
    console.log(error);
}

var Visio = {
    from: null,
    localVideo: null,
    remoteVideo: null,

    init: function() {
        Visio.from = MovimUtils.urlParts().params.join('/');
        Visio.localVideo = document.getElementById('video');
        Visio.remoteVideo = document.getElementById('remote_video');

        /*const servers = ['stun:stun01.sipphone.com',
            'stun:stun.ekiga.net',
            'stun:stun.fwdnet.net',
            'stun:stun.ideasip.com',
            'stun:stun.iptel.org',
            'stun:stun.rixtelecom.se',
            'stun:stun.schlund.de',
            'stun:stun.l.google.com:19302',
            'stun:stun1.l.google.com:19302',
            'stun:stun2.l.google.com:19302',
            'stun:stun3.l.google.com:19302',
            'stun:stun4.l.google.com:19302',
            'stun:stunserver.org',
            'stun:stun.softjoys.com',
            'stun:stun.voiparound.com',
            'stun:stun.voipbuster.com',
            'stun:stun.voipstunt.com',
            'stun:stun.voxgratia.org',
            'stun:stun.xten.com'
        ];

        const shuffled = servers.sort(() => 0.5 - Math.random());

        var configuration = {
            'iceServers': [
                {urls: shuffled.slice(0, 2)}
            ]
        };*/
        const configuration = {
            iceServers: [{
              urls: 'stun:stun.l.google.com:19302'
            }]
        };
        Visio.pc = new RTCPeerConnection(configuration);

        Visio.pc.onicecandidate = evt => {
            console.log('SEND CANDIDATE');
            if (evt.candidate) {
                console.log(evt.candidate.candidate);
                Visio_ajaxCandidate(evt.candidate, Visio.from);
            }
        };

        /*Visio.pc.ontrack = event => {
            console.log('TRACK');
            console.log(event);
            const stream = event.streams[0];
            if (!Visio.remoteVideo.srcObject || Visio.remoteVideo.srcObject.id !== stream.id) {
                console.log(event.track.kind);
                if (event.track.kind === 'video') Visio.remoteVideo.srcObject = stream;
            }
        };*/

        Visio.pc.onaddstream = function(event) {
            Visio.remoteVideo.srcObject = event.stream;
        };

        const remoteSDP = localStorage.getItem('sdp');

        // If we are calling
        if (remoteSDP === null) {
            console.log('CALLING');
            Visio.pc.onnegotiationneeded = function() {
                Visio.pc.createOffer().then(function(offer) {
                  return Visio.pc.setLocalDescription(offer);
                })
                .then(function() {
                    Visio_ajaxInitiate(Visio.pc.localDescription, Visio.from);
                });
            }
        } else {
            console.log('CALLED');
            // If we are called
            localStorage.removeItem('sdp');
            Visio.pc.setRemoteDescription(new RTCSessionDescription({'sdp': remoteSDP + "\n", 'type': 'offer'}), () => {
                if (Visio.pc.remoteDescription.type === 'offer') {
                    Visio.pc.createAnswer().then(function(answer) {
                        return Visio.pc.setLocalDescription(answer);
                    }).then(function() {
                        Visio.consumeCandidates();
                        Visio_ajaxAccept(Visio.pc.localDescription, Visio.from);
                    }).catch(logError);
                }
            }, logError);
        }

        navigator.mediaDevices.getUserMedia({
            audio: true,
            video: true,
        }).then(stream => {
            Visio.localVideo.srcObject = stream;
            stream.getTracks().forEach(track => Visio.pc.addTrack(track, stream));
        }, logError);
    },

    consumeCandidates: function() {
        const candidates = localStorage.getObject('candidates');

        if (candidates) {
            candidates.forEach(candidate => Visio.onCandidate(candidate[0], candidate[1], candidate[2]));
            //localStorage.removeItem('candidates');
        }
    },

    onCandidate: function(candidate, mid, mlineindex) {
        if (mid == '') mlineindex = 1;

        console.log('RECEIVED CANDIDATE');
        if (Visio.pc.remoteDescription == null) return;
        console.log(candidate);

        candidate = new RTCIceCandidate({
            'candidate': candidate,
            'sdpMid': mid,
            'sdpMLineIndex' : mlineindex
        });

        Visio.pc.addIceCandidate(candidate, e => {});
    },

    onAcceptSDP: function(sdp) {
        Visio.pc.setRemoteDescription(
            new RTCSessionDescription({'sdp': sdp + "\n", 'type': 'answer'}), () => {
                Visio.consumeCandidates();
            },
            (error) => {
                Visio.goodbye('incompatible-parameters');
                logError(error)
            }
        );
    },

    onTerminate: () => {
        let localStream = Visio.localVideo.srcObject;

        if (localStream) {
            localStream.getTracks().forEach(track => track.stop());
        }

        let remoteStream = Visio.remoteVideo.srcObject;

        if (remoteStream) {
            remoteStream.getTracks().forEach(track => track.stop());
        }

        Visio.localVideo.srcObject = null;
        Visio.remoteVideo.srcObject = null;
        Visio.localVideo.classList.add('hide');

        if (Visio.pc) Visio.pc.close();

        document.querySelector('p.state').innerText = Visio.states.ended;
        button = document.querySelector('#main');

        button.className = 'button action color red';
        button.querySelector('i').className = 'material-icons';
        button.querySelector('i').innerText = 'close';

        button.onclick = () => window.close();
    },

    goodbye: (reason) => {
        Visio.onTerminate();
        Visio_ajaxTerminate(Visio.from, reason);
    },
}

MovimWebsocket.attach(() => {
    Visio.init();
});

window.onbeforeunload = () => {
    Visio.goodbye();
}