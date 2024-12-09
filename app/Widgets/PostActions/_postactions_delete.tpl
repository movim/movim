<section>
    <h3>{$c->__('post.delete_title')}</h3>
    <br />
    {if="$post->isComment()"}
    <h4 class="gray">{$c->__('post.delete_comment')}</h4>
    {else}
        <h4 class="gray">{$c->__('post.delete_text')}</h4>
    {/if}
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat"
        onclick="PostActions_ajaxDeleteConfirm('{$post->server}', '{$post->node}', '{$post->nodeid}'); Dialog_ajaxClear()">
        {$c->__('button.delete')}
    </button>
</footer>
