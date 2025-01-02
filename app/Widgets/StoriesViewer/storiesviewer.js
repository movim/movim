var StoriesViewer = {
    story: undefined,
    timer: undefined,

    timeout: 6000,
    startDate: undefined,
    consumed: 0,

    before: undefined,

    launch: function(before) {
        MovimTpl.pushAnchorState('stories', function () {
            StoriesViewer.close();
        });

        StoriesViewer.story = document.querySelector('article.story');
        StoriesViewer.before = before;

        let image = StoriesViewer.story.querySelector('img');
        image.addEventListener('mousedown', e => { e.preventDefault(); StoriesViewer.pause()});
        image.addEventListener('touchstart', e => { e.preventDefault(); StoriesViewer.pause()});
        image.addEventListener('mouseup', e => { e.preventDefault(), StoriesViewer.start()});
        image.addEventListener('touchend', e => { e.preventDefault(), StoriesViewer.start()});

        let next = StoriesViewer.story.querySelector('div.next');
        next.addEventListener('click', e => StoriesViewer_ajaxHttpGetNext(StoriesViewer.before));

        StoriesViewer.reset();
        StoriesViewer.start();
    },

    reset: function() {
        clearTimeout(StoriesViewer.timer);
        StoriesViewer.consumed = 0;
    },

    close: function() {
        StoriesViewer.reset();
        StoriesViewer_ajaxClose();
        Stories_ajaxHttpGet();
    },

    start: function () {
        if (!StoriesViewer.story) return;

        StoriesViewer.story.classList.remove('paused');
        StoriesViewer.startDate = new Date();

        StoriesViewer.timer = setTimeout(function () {
            StoriesViewer_ajaxHttpGetNext(StoriesViewer.before);
        }, StoriesViewer.timeout - StoriesViewer.consumed);
    },

    pause: function() {
        if (!StoriesViewer.story) return;

        StoriesViewer.story.classList.add('paused');
        StoriesViewer.consumed += new Date() - StoriesViewer.startDate;

        clearTimeout(StoriesViewer.timer);
    },

    sendComment: function (storyId) {
        StoriesViewer_ajaxSendComment(storyId, document.querySelector('form[name=storycomment] input[name=comment]').value);
        document.querySelector('form[name=storycomment]').reset();
    }
}
