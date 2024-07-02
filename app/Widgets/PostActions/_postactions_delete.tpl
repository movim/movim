<section>
    <h3>{$c->__('post.delete_title')}</h3>
    <br />
    {if="$post->isComment()"}
    <h4 class="gray">{$c->__('post.delete_comment')}</h4>
    {else}
        <h4 class="gray">{$c->__('post.delete_text')}</h4>
    {/if}
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat"
        onclick="PostActions_ajaxDeleteConfirm('{$to}', '{$node}', '{$id}'); Dialog_ajaxClear()">
        {$c->__('button.delete')}
    </button>
</div>
