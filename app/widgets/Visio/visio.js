function logError(error) {
    console.log(error.name + ': ' + error.message);
}

var Visio = {
    pc: null,
    constraints: null,
    audioContext: null,
    max_level_L: 0,
    old_level_L: 0,
    from: null,
    type: null,
    start: false,

    setFrom: function(from) {
        Visio.from = from;
    },


    /*
     * Jingle and WebRTC
     */
    handleSuccess: function(stream) {
        Visio.pc.addStream(stream);

        Visio.toggleMainButton();

        Visio_ajaxGetSDP();

        // Video
        var videoTracks = stream.getVideoTracks();
        console.log('Got stream with constraints:', constraints);
        console.log('Using video device: ' + videoTracks[0].label);

        stream.oninactive = function() {
            console.log('Stream inactive');
        };

        window.stream = stream; // make variable available to browser console
        document.getElementById('video').srcObject = stream;

        // Audio
        var microphone = Visio.audioContext.createMediaStreamSource(stream);
        var javascriptNode = Visio.audioContext.createScriptProcessor(2048, 1, 1);

        var cnvs = document.querySelector('#visio .level');
        var cnvs_cntxt = cnvs.getContext("2d");

        microphone.connect(javascriptNode);
        javascriptNode.connect(Visio.audioContext.destination);
        javascriptNode.onaudioprocess = function(event){
            var inpt_L = event.inputBuffer.getChannelData(0);
            var instant_L = 0.0;

            var sum_L = 0.0;

            for(var i = 0; i < inpt_L.length; ++i) {
                sum_L += inpt_L[i] * inpt_L[i];
            }

            instant_L = Math.sqrt(sum_L / inpt_L.length);
            Visio.max_level_L = Math.max(Visio.max_level_L, instant_L);
            instant_L = Math.max( instant_L, Visio.old_level_L -0.008 );
            Visio.old_level_L = instant_L;

            cnvs_cntxt.clearRect(0, 0, cnvs.width, cnvs.height);
            cnvs_cntxt.fillStyle = 'white';
            cnvs_cntxt.fillRect(0, 0,(cnvs.width)*(instant_L/Visio.max_level_L),(cnvs.height)); // x,y,w,h
        }
    },

    onSDP: function(sdp, type) {
        Visio.type = type;

        console.log('SDP');
        console.log(sdp);

        Visio.pc.setRemoteDescription(
            new RTCSessionDescription({'sdp': sdp + "\n", 'type': type}),
            function () {
                Visio_ajaxGetCandidates();

                // if we received an offer, we need to answer
                if (Visio.pc.remoteDescription.type == 'offer')
                    Visio.pc.createAnswer(Visio.localDescCreated, logError);
            },
            logError
        );
    },

    localDescCreated: function(desc) {
        Visio.pc.setLocalDescription(desc, Visio.toggleMainButton, logError);
    },

    onCandidate: function(candidate, mid, mlineindex) {
        console.log('candidate');
        console.log(candidate);

        Visio_ajaxGetCandidates();

        if(mid == '') mlineindex = 1;

        if(Visio.pc.remoteDescription == null) return;

        candidate = new RTCIceCandidate(
            {
                'candidate': candidate,
                'sdpMid': mid,
                'sdpMLineIndex' : mlineindex
            });

        Visio.pc.addIceCandidate(candidate);
    },

    onTerminate: function() {
        console.log('terminate');
        Visio.pc.getRemoteStreams().forEach(function(stream) {
            stream.getTracks().forEach(function(track) {
                track.stop();
            });
        });

        document.getElementById('video').srcObject = null;
        document.getElementById('remote_video').srcObject = null;

        Visio.pc.close();

        if(window.opener) {
            window.close();
        }
    },

    init: function(start) {
        Visio.start = start;

        Visio.toggleMainButton();

        Visio.setFrom(MovimUtils.urlParts().params.join('/'));

        var configuration = {
            'iceServers': [
                {urls: ['stun:stun01.sipphone.com',
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
                'stun:stun.xten.com']}
            ]
        };

        // WebRTC
        if(typeof webkitRTCPeerConnection == 'function') {
            Visio.pc = new webkitRTCPeerConnection(configuration);
        } else {
            Visio.pc = new RTCPeerConnection(configuration);
        }

        Visio.pc.onicecandidate = function (evt) {
            Visio.toggleMainButton();

            if (evt.candidate) {
                Visio_ajaxCandidate(evt.candidate, Visio.from);
            }
        };

        Visio.pc.oniceconnectionstatechange = function () {
            Visio.toggleMainButton();
        };

        Visio.pc.onicegatheringstatechange = function () {
            Visio.toggleMainButton();
        };

        /*Visio.pc.ontrack = function (evt) {
            document.getElementById('remote_video').src = URL.createObjectURL(evt.streams[0]);
        };*/
        Visio.pc.onaddstream = function(evt) {
            document.getElementById('remote_video').src = URL.createObjectURL(evt.stream);
        };

        Visio.audioContext = new AudioContext();

        Visio.constraints = window.constraints = {
            audio: true,
            video: true
        };

        Visio.toggleMainButton();

        if(typeof navigator.webkitGetUserMedia == 'function') {
            navigator.webkitGetUserMedia(constraints, Visio.handleSuccess, logError);
        } else {
            navigator.mediaDevices.getUserMedia(constraints).
            then(Visio.handleSuccess).catch(logError);
        }
    },

    /*
     * Actions
     */
    hello: function() {
        Visio.pc.createOffer(function (desc) {
            Visio.pc.setLocalDescription(desc, function () {
                Visio_ajaxInitiate(Visio.pc.localDescription, Visio.from);
            }, logError);
        }, logError);
    },

    answer: function() {
        Visio_ajaxAccept(Visio.pc.localDescription, Visio.from);
    },

    goodbye: function() {
        Visio.onTerminate();

        Visio_ajaxTerminate(Visio.from);
    },

    /*
     * UI Status
     */
    toggleMainButton: function() {
        button = document.getElementById('main');

        i = button.querySelector('i');

        MovimUtils.removeClass(button, 'red');
        MovimUtils.removeClass(button, 'green');
        MovimUtils.removeClass(button, 'gray');
        MovimUtils.removeClass(button, 'orange');
        MovimUtils.removeClass(button, 'ring');

        MovimUtils.addClass(button, 'disabled');

        if(Visio.pc) {
            if(Visio.pc.iceConnectionState != 'closed'
            && Visio.pc.getLocalStreams().length > 0) {
                MovimUtils.removeClass(button, 'disabled');
            }

            if(Visio.pc.iceConnectionState == 'new') {
                if(Visio.pc.iceGatheringState == 'gathering'
                || Visio.pc.iceGatheringState == 'complete') {
                    MovimUtils.addClass(button, 'orange');
                    i.className = 'zmdi zmdi-phone-ring';

                    button.onclick = function() { Visio.goodbye(); };
                } else {
                    MovimUtils.addClass(button, 'green');
                    i.className = 'zmdi zmdi-phone';

                    button.onclick = function() { Visio.hello(); };
                }
            } else if(Visio.pc.iceConnectionState == 'checking') {
                MovimUtils.addClass(button, 'green');
                i.className = 'zmdi zmdi-phone-end ring';

                button.onclick = function() { Visio.answer(); };
            } else if(Visio.pc.iceConnectionState == 'closed') {
                MovimUtils.addClass(button, 'gray');
                i.className = 'zmdi zmdi-phone-end';

                button.onclick = function() { MovimUtils.reloadThis(); };
            } else if(Visio.pc.iceConnectionState == 'connected'
                   || Visio.pc.iceConnectionState == 'complete'
                   || Visio.pc.iceConnectionState == 'failed') {
                MovimUtils.addClass(button, 'red');
                i.className = 'zmdi zmdi-phone-end';

                button.onclick = function() { Visio.goodbye(); };
            }
        }
    },

    toggleFullScreen: function() {
        var button = document.querySelector('#toggle_fullscreen i');

        if (!document.fullscreenElement
        && !document.msFullscreenElement
        && !document.mozFullScreenElement
        && !document.webkitFullscreenElement) {
            if (document.body.requestFullscreen) {
                document.body.requestFullscreen();
            } else if (document.body.msRequestFullscreen) {
                document.body.msRequestFullscreen();
            } else if (document.body.mozRequestFullScreen) {
                document.body.mozRequestFullScreen();
            } else if (document.body.webkitRequestFullscreen) {
                document.body.webkitRequestFullscreen();
            }

            button.className = 'zmdi zmdi-fullscreen-exit';
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitCancelFullScreen) {
                document.webkitCancelFullScreen();
            }

            button.className = 'zmdi zmdi-fullscreen';
        }
    },

    toggleAudio: function() {
        var button = document.querySelector('#toggle_audio i');

        if(Visio.pc.getLocalStreams()[0].getAudioTracks()[0].enabled) {
            Visio.pc.getLocalStreams()[0].getAudioTracks()[0].enabled = 0;
            button.className = 'zmdi zmdi-volume-off';
        } else {
            Visio.pc.getLocalStreams()[0].getAudioTracks()[0].enabled = 1;
            button.className = 'zmdi zmdi-volume-up';
        }
    },

    toggleVideo: function() {
        var button = document.querySelector('#toggle_video i');

        if(Visio.pc.getLocalStreams()[0].getVideoTracks()[0].enabled) {
            Visio.pc.getLocalStreams()[0].getVideoTracks()[0].enabled = 0;
            button.className = 'zmdi zmdi-eye-off';
        } else {
            Visio.pc.getLocalStreams()[0].getVideoTracks()[0].enabled = 1;
            button.className = 'zmdi zmdi-eye';
        }
    },
}

MovimWebsocket.attach(function() {
    Visio.init();
});

window.onbeforeunload = function() {
    Visio.goodbye();
}

