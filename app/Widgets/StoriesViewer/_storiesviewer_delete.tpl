<section>
    <h3>{$c->__('stories.delete_title')}</h3>
    <br />
    <h4 class="gray">{$c->__('stories.delete_text')}</h4>
</section>
<footer>
    <button onclick="StoriesViewer_ajaxStart(); Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat"
        onclick="StoriesViewer_ajaxDeleteConfirm('{$post->id}'); Dialog_ajaxClear()">
        {$c->__('button.delete')}
    </button>
</footer>
