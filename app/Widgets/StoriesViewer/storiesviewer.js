var StoriesViewer = {
    story: undefined,
    timer: undefined,

    timeout: 6000,
    startTimeout: undefined,
    timeLeft: 0,
    before: undefined,

    launch: function(before) {
        MovimTpl.pushAnchorState('stories', function () {
            StoriesViewer.close();
        });

        StoriesViewer.story = document.querySelector('article.story');
        StoriesViewer.before = before;
        clearTimeout(StoriesViewer.timer);

        let image = StoriesViewer.story.querySelector('img');
        image.addEventListener('mousedown', e => { e.preventDefault(); StoriesViewer.pause()});
        image.addEventListener('touchstart', e => { e.preventDefault(); StoriesViewer.pause()});
        image.addEventListener('mouseup', e => { e.preventDefault(), StoriesViewer.resume()});
        image.addEventListener('touchend', e => { e.preventDefault(), StoriesViewer.resume()});


        let next = StoriesViewer.story.querySelector('div.next');
        next.addEventListener('click', e => StoriesViewer_ajaxGetNext(StoriesViewer.before));

        StoriesViewer.start();
    },

    close: function() {
        clearTimeout(StoriesViewer.timer);
        StoriesViewer_ajaxClose();
        Stories_ajaxHttpGet();
    },

    start: function (before) {
        StoriesViewer.startTimeout = new Date();
        StoriesViewer.timeLeft = 0;

        StoriesViewer.timer = setTimeout(function () {
            StoriesViewer_ajaxGetNext(StoriesViewer.before);
        }, StoriesViewer.timeout);
    },

    pause: function() {
        StoriesViewer.story.classList.add('paused');
        StoriesViewer.timeLeft = StoriesViewer.timeout;
        StoriesViewer.timeLeft -= new Date() - StoriesViewer.startTimeout;

        clearTimeout(StoriesViewer.timer);
    },

    resume: function () {
        StoriesViewer.story.classList.remove('paused');
        if( !StoriesViewer.timeLeft ) { StoriesViewer.timeLeft = StoriesViewer.timeDelay; }

        StoriesViewer.timer = setTimeout(function () {
            StoriesViewer_ajaxGetNext(StoriesViewer.before);
        }, StoriesViewer.timeLeft);
    },

    sendComment: function (storyId) {
        StoriesViewer_ajaxSendComment(storyId, document.querySelector('form[name=storycomment] input[name=comment]').value);
        document.querySelector('form[name=storycomment]').reset();
    }
}
