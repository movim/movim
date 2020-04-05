function logError(error) {
    console.log(error.name + ': ' + error.message);
    console.error(error);
}

var Visio = {
    from: null,
    id: null,
    localVideo: null,
    remoteVideo: null,

    calling: false,

    videoSelect: undefined,
    switchCamera: undefined,

    init: function(id) {
        Visio.from = MovimUtils.urlParts().params[0] + '/' + MovimUtils.urlParts().params[1];
        Visio.localVideo = document.getElementById('video');
        Visio.remoteVideo = document.getElementById('remote_video');

        const servers = ['stun:stun01.sipphone.com',
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
        };

        Visio.pc = new RTCPeerConnection(configuration);

        Visio.pc.ontrack = event => {
            Visio.remoteVideo.srcObject = event.streams[0];
        };

        Visio.pc.onicecandidate = event => {
            if (event.candidate) {
                Visio_ajaxCandidate(event.candidate, Visio.from, Visio.id);
            }
        };

        Visio.pc.oniceconnectionstatechange = () => VisioUtils.toggleMainButton();

        Visio.pc.onicegatheringstatechange = function (event) {
            // When we didn't receive the WebRTC termination before Jingle
            if (Visio.pc.iceConnectionState == 'disconnected') {
                Visio.onTerminate();
            }

            VisioUtils.toggleMainButton();
        };

        VisioUtils.toggleMainButton();
        VisioUtils.switchCameraSetup();
    },

    gotStream: function() {
        // On Android where you can't have both camera enabled at the same time
        var videoTrack = Visio.pc.getSenders().find(rtc => rtc.track && rtc.track.kind == 'video');
        if (videoTrack) videoTrack.track.stop();

        Visio.switchCamera.classList.add('disabled');

        navigator.mediaDevices.getUserMedia({
            audio: true,
            video: {
                deviceId: Visio.videoSelect.value,
                facingMode: 'user',
                width: { ideal: 4096 },
                height: { ideal: 4096 }
            }
        }).then(stream => {
            Visio.switchCamera.classList.remove('disabled');

            Visio.localVideo.srcObject = stream;

            // Switch camera
            let videoTrack = stream.getVideoTracks()[0];
            var sender = Visio.pc.getSenders().find(s => s.track && s.track.kind == videoTrack.kind);

            if (sender) {
                sender.replaceTrack(videoTrack);
            }

            VisioUtils.handleAudio();
            VisioUtils.toggleMainButton();

            // For the first time we attach all the tracks and we launch the call
            if (Visio.pc.getSenders().length == 0) {
                stream.getTracks().forEach(track => Visio.pc.addTrack(track, stream));

                if (Visio.id) {
                    Visio_ajaxAccept(Visio.from, Visio.id);
                } else {
                    // TODO launch when button pressed
                    Visio.id = Math.random().toString(36).substr(2, 9);
                    Visio.calling = true;
                    VisioUtils.toggleMainButton();
                    Visio_ajaxPropose(Visio.from, Visio.id);
                }
            }
        }, logError);
    },

    onCandidate: function(candidate, mid, mlineindex) {
        Visio.pc.addIceCandidate(new RTCIceCandidate({
            'candidate': candidate.substr(2),
            'sdpMid': mid,
            'sdpMLineIndex' : mlineindex
        }), () => {}, logError);
    },

    onProceed: function(from, id) {
        if (Visio.from == from && Visio.id == id) {
            Visio.pc.createOffer().then(function(offer) {
                Visio.calling = false;
                VisioUtils.toggleMainButton();
                return Visio.pc.setLocalDescription(offer);
            })
            .then(function() {
                Visio_ajaxSessionInitiate(Visio.pc.localDescription, Visio.from, Visio.id);
            });
        } else {
            console.error('Wrong call')
        }
    },

    onInitiateSDP: function(sdp) {
        Visio.pc.setRemoteDescription(new RTCSessionDescription({'sdp': sdp + "\n", 'type': 'offer'}), () => {
            Visio.pc.createAnswer().then(function(answer) {
                return Visio.pc.setLocalDescription(answer);
            }).then(function() {
                Visio_ajaxSessionAccept(Visio.pc.localDescription, Visio.from, Visio.id);
            }).catch(logError);
        }, logError);
    },

    onAcceptSDP: function(sdp) {
        Visio.pc.setRemoteDescription(
            new RTCSessionDescription({'sdp': sdp + "\n", 'type': 'answer'}), () => {},
            (error) => {
                Visio.goodbye('incompatible-parameters');
                logError(error)
            }
        );
    },

    onTerminate: (reason) => {
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

        document.querySelector('p.state').innerText = reason == 'decline'
            ? Visio.states.declined
            : Visio.states.ended;
        button = document.querySelector('#main');

        button.className = 'button action color red';
        button.querySelector('i').className = 'material-icons';
        button.querySelector('i').innerText = 'close';

        button.onclick = () => window.close();
    },

    goodbye: (reason) => {
        Visio.onTerminate(reason);
        Visio_ajaxTerminate(Visio.from, reason);
    },
}

MovimWebsocket.attach(() => {
    if (MovimUtils.urlParts().params[2] !== undefined) {
        Visio.id = MovimUtils.urlParts().params[2];
    }

    Visio.init();
});

window.onbeforeunload = () => {
    Visio.goodbye();
}
