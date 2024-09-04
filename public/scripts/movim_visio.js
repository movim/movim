var MovimVisio = {
    from: null,
    id: null,
    withVideo: false,

    pc: null,
    services: [],

    localStream: null,

    localVideo: null,
    remoteVideo: null,
    localAudio: null,
    remoteAudio: null,
    screenSharing: null,

    inboundStream: null,

    observer: null,

    load: function() {
        MovimVisio.localVideo = document.getElementById('local_video');
        MovimVisio.localVideo.addEventListener('loadeddata', () => {
            MovimVisio.localVideo.play()
        });
        MovimVisio.remoteVideo = document.getElementById('remote_video');
        MovimVisio.remoteVideo.disablePictureInPicture = true;
        MovimVisio.screenSharing = document.getElementById('screen_sharing_video');

        MovimVisio.localAudio = document.getElementById('local_audio');
        MovimVisio.remoteAudio = document.getElementById('remote_audio');
    },

    clear: function () {
        MovimVisio.from = null;
        MovimVisio.id = null;
        MovimVisio.withVideo = false;

        if (MovimVisio.pc) {
            MovimVisio.pc.close();
            MovimVisio.pc = null;
        }

        if (MovimVisio.localStream) {
            MovimVisio.localStream.getTracks().forEach(function (track) {
                track.stop();
            });
            MovimVisio.localStream = null;
        }

        MovimVisio.localAudio = null;
        MovimVisio.remoteAudio = null;
        MovimVisio.localVideo = null;
        MovimVisio.remoteVideo = null;
        MovimVisio.screenSharing = null;

        MovimVisio.inboundStream = null;
    },

    moveToChat: function (jid) {
        if (MovimVisio.observer != null) {
            MovimVisio.observer.disconnect();
        }

        var parts = MovimUtils.urlParts();
        if (parts.page != 'chat' || parts.params[0] != jid) {
            return;
        }

        const visio = document.getElementById('visio');
        const body = document.body;

        document.querySelector('#chat_widget header').after(visio);

        const callback = (mutationList, observer) => {
            if (!document.getElementById('visio')) {
                document.getElementById('endcommon').before(visio);
            }
        };

        MovimVisio.observer = new MutationObserver(callback);
        MovimVisio.observer.observe(body, { childList: true, subtree: true });
    }
}