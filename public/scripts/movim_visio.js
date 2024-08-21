var MovimVisio = {
    from: null,
    id: null,
    withVideo: false,

    pc: null,
    services: [],

    localVideo: null,
    remoteVideo: null,
    localAudio: null,
    remoteAudio: null,
    screenSharing: null,

    inboundStream: null,

    observer: null,

    clear: function () {
        MovimVisio.from = null;
        MovimVisio.id = null;
        MovimVisio.withVideo = false;

        MovimVisio.pc.close();

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