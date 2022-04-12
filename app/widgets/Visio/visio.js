function logError(error) {
    console.log(error.name + ': ' + error.message);
    console.error(error);
}

var Visio = {
    from: null,
    id: null,
    withVideo: false,

    localVideo: null,
    remoteVideo: null,
    localAudio: null,
    remoteAudio: null,
    screenSharing: null,

    calling: false,

    videoSelect: undefined,
    switchCamera: undefined,

    inboundStream: null,

    services: [],

    tracksTypes: [],

    init: function() {
        Visio.from = MovimUtils.urlParts().params[0];

        if (MovimUtils.urlParts().params[1] !== undefined) {
            Visio.from += '/' + MovimUtils.urlParts().params[1];
        }

        if (Visio.withVideo) {
            Visio.localVideo = document.getElementById('video');
            Visio.remoteVideo = document.getElementById('remote_video');
            Visio.screenSharing = document.getElementById('screen_sharing_video');
        }

        Visio.localAudio = document.getElementById('audio');
        Visio.remoteAudio = document.getElementById('remote_audio');

        var configuration = {
            'iceServers': Visio.services
        };

        Visio.pc = new RTCPeerConnection(configuration);

        Visio.pc.ontrack = event => {
            if (Visio.withVideo) {
                if (event.streams && event.streams[0]) {
                    Visio.remoteVideo.srcObject = event.streams[0];
                } else {
                    if (!Visio.inboundStream) {
                        Visio.inboundStream = new MediaStream();
                        Visio.remoteVideo.srcObject = Visio.inboundStream;
                    }
                    Visio.inboundStream.addTrack(event.track);
                }

                VisioUtils.setRemoteAudioState('mic');
                VisioUtils.setRemoteVideoState('videocam');
            } else {
                if (event.streams && event.streams[0]) {
                    Visio.remoteAudio.srcObject = event.streams[0];
                } else {
                    if (!Visio.inboundStream) {
                        Visio.inboundStream = new MediaStream();
                        Visio.remoteAudio.srcObject = Visio.inboundStream;
                    }
                    Visio.inboundStream.addTrack(event.track);
                }
                VisioUtils.handleRemoteAudio();
                VisioUtils.setRemoteAudioState('mic');
            }

            Visio.tracksTypes['mid'+event.transceiver.mid] = event.track.kind;
        };

        Visio.pc.onicecandidate = event => {
            let candidate = event.candidate;
            if (candidate && candidate.candidate && candidate.candidate.length > 0) {
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

        if (Visio.withVideo) {
            VisioUtils.switchCameraSetup();
        }

        Visio.getStream();
    },

    onMute: function(name) {
        if (Visio.tracksTypes[name]) {
            if (Visio.tracksTypes[name] == 'audio') {
                VisioUtils.setRemoteAudioState('mic_off');
            }

            if (Visio.tracksTypes[name] == 'video') {
                document.querySelector('#remote_video').classList.add('muted');
                VisioUtils.setRemoteVideoState('videocam_off');
            }
        }
    },

    onUnmute: function(name) {
        if (Visio.tracksTypes[name]) {
            if (Visio.tracksTypes[name] == 'audio') {
                VisioUtils.setRemoteAudioState('mic');
            }

            if (Visio.tracksTypes[name] == 'video') {
                document.querySelector('#remote_video').classList.remove('muted');
                VisioUtils.setRemoteVideoState('videocam');
            }
        }
    },

    setServices: function(services) {
        Visio.services = services;
    },

    getStream: function() {
        if (Visio.withVideo) {
            // On Android where you can't have both camera enabled at the same time
            var videoTrack = Visio.pc.getSenders().find(rtc => rtc.track && rtc.track.kind == 'video');
            if (videoTrack) videoTrack.track.stop();

            Visio.switchCamera.classList.add('disabled');
        }

        var constraints = {
            audio: true,
            video: false
        };

        if (localStorage.getItem('defaultMicrophone')) {
            constraints.audio = {
                deviceId: localStorage.getItem('defaultMicrophone')
            }
        }

        if (Visio.withVideo) {
            const videoSource = Visio.videoSelect.value;
            var defaultCamera = undefined;

            if (localStorage.getItem('defaultCamera')) {
                defaultCamera = localStorage.getItem('defaultMicrophone');
            }

            constraints.video = {
                deviceId: videoSource ? {exact: videoSource} : defaultCamera,
                facingMode: 'user',
                width: { ideal: 4096 },
                height: { ideal: 4096 }
            };
        }

        navigator.mediaDevices.getUserMedia(constraints).then(stream => {
            var tracks = stream.getTracks();
            for(var i = 0; i < tracks.length; i++){
                if (tracks[i].getSettings().channelCount) {
                    localStorage.setItem('defaultMicrophone', tracks[i].getSettings().deviceId);
                } else {
                    localStorage.setItem('defaultCamera', tracks[i].getSettings().deviceId);
                }
            }

            if (!Visio.withVideo) {
                Visio.localAudio.srcObject = stream;
            } else {
                Visio.switchCamera.classList.remove('disabled');
                Visio.localVideo.srcObject = stream;

                // Toggle video icon
                var cameraIcon = document.querySelector('#toggle_video i');
                cameraIcon.innerText = 'videocam';

                // Switch camera
                VisioUtils.pcReplaceTrack(stream);
                VisioUtils.enableScreenSharingButton();
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
                    Visio_ajaxPropose(Visio.from, Visio.id, Visio.withVideo);
                }
            }
        }, logError);
    },

    gotQuickStream: function() {
        VisioUtils.pcReplaceTrack(Visio.localVideo.srcObject);
    },

    gotScreen: function() {
        VisioUtils.pcReplaceTrack(Visio.screenSharing.srcObject);
    },

    onCandidate: function(candidate, mid, mlineindex) {
        // filter the a=candidate lines
        var filtered = candidate.split(/\n/).filter(line => {
            return line.startsWith('a=candidate');
        });

        Visio.pc.addIceCandidate(new RTCIceCandidate({
            'candidate': filtered.join('').substr(2),
            'sdpMid': mid,
            'sdpMLineIndex' : mlineindex
        }), () => {}, logError);
    },

    onProceed: function(from, id) {
        if (from.substring(0, Visio.from.length) == Visio.from && Visio.id == id) {
            // We set the remote resource
            Visio.from = from;

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
        if (!Visio.withVideo) {
            let localStream = Visio.localAudio.srcObject;

            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
            }

            let remoteStream = Visio.remoteAudio.srcObject;

            if (remoteStream) {
                remoteStream.getTracks().forEach(track => track.stop());
            }

            Visio.localAudio.srcObject = null;
            Visio.remoteAudio.srcObject = null;
        } else {
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
        }


        if (Visio.pc) Visio.pc.close();

        document.querySelector('p.state').innerText = reason == 'decline'
            ? Visio.states.declined
            : Visio.states.ended;
        button = document.querySelector('#main');

        button.className = 'button action color red';
        button.querySelector('i').className = 'material-icons';
        button.querySelector('i').innerText = 'close';

        button.onclick = () => {
            window.close();
        }

        // And we force close the window after 2sec
        window.setTimeout(() => {
            window.close();
        }, 2000);
    },

    goodbye: (reason) => {
        Visio.onTerminate(reason);
        Visio_ajaxTerminate(Visio.from, Visio.id, reason);
    },
}

MovimWebsocket.attach(() => {
    if (MovimUtils.urlParts().params[2] !== undefined) {
        Visio.id = MovimUtils.urlParts().params[2];
    }

    if (MovimUtils.urlParts().page == 'visio') {
        Visio.withVideo = true;
    }

    Visio_ajaxResolveServices();
});

window.onbeforeunload = () => {
    Visio.goodbye();
}
