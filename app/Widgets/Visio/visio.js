function logError(error) {
    console.log(error.name + ': ' + error.message);
    console.error(error);
}

var Visio = {
    calling: false,

    videoSelect: undefined,
    switchCamera: undefined,

    states: null,

    participants: [],

    // TODO Remove MovimVisio.withVideo ?
    init: function (fullJid, jid, id, withVideo, isMuji) {
        Visio_ajaxPrepare(jid);

        MovimVisio.id = id;
        //MovimVisio.muji = muji ?? false;

        let visio = document.querySelector('#visio');
        delete visio.dataset.type; // TODO reset me at the end
        visio.dataset.jid = jid;
        visio.dataset.type = (withVideo) ? 'video' : 'audio';

        if (isMuji == true) {
            MovimVisio.mujiInit();
        } else {
            MovimJingles.initSession(jid, fullJid, id);

            // Called
            if (MovimVisio.id) {
                Visio_ajaxAccept(fullJid, MovimVisio.id);

            // Calling
            } else {
                MovimVisio.id = crypto.randomUUID();
                Visio.calling = true; // TODO, remove me ?
                //VisioUtils.toggleMainButton();
                Visio_ajaxPropose(jid, MovimVisio.id, withVideo);
            }
        }

        if (!withVideo) {
            //MovimJingles.replaceLocalStream(MovimVisio.localStream);
        }
    },

    initMujiParticipant: function (fullJid, jid, mujiRoom) {
        // Bug, only because we can receive several presences
        if (!Visio.participants.includes(jid)) {
            let visio = document.querySelector('#visio');
            Visio.calling = true;
            Visio.participants.push(jid);
            //Visio_ajaxPropose(jid, crypto.randomUUID(), visio.dataset.type);
            let id = MovimJingles.initSession(jid, fullJid);
            MovimJingles.onProceed(jid, fullJid, id, mujiRoom);
        }
    },

    //init: function (bareFrom) {
        /*visio.dataset.from = bareFrom;*/

        //MovimVisio.load();

        //MovimVisio.pc = new RTCPeerConnection({ 'iceServers': MovimVisio.services });

        /*MovimVisio.pc.ontrack = event => {
            var srcObject = null;

            if (event.streams && event.streams[0]) {
                srcObject = event.streams[0];
            } else {
                if (!MovimVisio.inboundStream) {
                    MovimVisio.inboundStream = new MediaStream();
                    MovimVisio.remoteAudio.srcObject = MovimVisio.inboundStream;
                }

                MovimVisio.inboundStream.addTrack(event.track);
                srcObject = MovimVisio.inboundStream;
            }

            VisioUtils.setRemoteVideoState('');

            if (event.track.kind == 'audio') {
                MovimVisio.remoteAudio.srcObject = srcObject;
                VisioUtils.setRemoteAudioState('mic');
            } else if (event.track.kind == 'video') {
                MovimVisio.remoteVideo.srcObject = srcObject;
                VisioUtils.setRemoteVideoState('videocam');
            }

            VisioUtils.handleRemoteAudio();
            Visio.tracksTypes['mid' + event.transceiver.mid] = event.track.kind;
        };*/

        /*if (MovimVisio.muji == false) {
            MovimVisio.pc.onicecandidate = event => {
                let candidate = event.candidate;
                if (candidate && candidate.candidate && candidate.candidate.length > 0) {
                    Visio_ajaxCandidate(event.candidate, MovimVisio.from, MovimVisio.id);
                }
            };
        }*/

        /*MovimVisio.pc.oniceconnectionstatechange = () => VisioUtils.toggleMainButton();

        MovimVisio.pc.onicegatheringstatechange = function (event) {
            // When we didn't receive the WebRTC termination before Jingle
            if (MovimVisio.pc.iceConnectionState == 'disconnected') {
                Visio.onTerminate();
            }

            VisioUtils.toggleMainButton();
        };*/

        //VisioUtils.toggleMainButton();

        /*if (MovimVisio.withVideo) {
            VisioUtils.switchCameraInCall();
        }*/
    //},

    setServices: function (services) {
        MovimVisio.services = services;
    },

    setStates: function (states) {
        Visio.states = states;
    },

    onTerminate: () => {
        if (VisioUtils.audioContext) {
            VisioUtils.audioContext.close();
            VisioUtils.audioContext = null;
        }
    },

    goodbye: (reason) => {
        Visio.onTerminate();
        MovimJingles.closeSessions();

        let visio = document.querySelector('#visio');
        delete visio.dataset.type;

        if (document.fullscreenElement) {
            document.exitFullscreen();
        }

        if (MovimVisio.id) {
            Visio_ajaxEnd(visio.dataset.jid, MovimVisio.id, reason);
        }

        delete visio.dataset.jid;

        MovimVisio.clear();
    },
}

MovimWebsocket.attach(() => {
    if (MovimVisio.services.length == 0) {
        Visio_ajaxResolveServices();
    }

    Visio_ajaxGetStates();
});
