<section>
    <h3>{$c->__('post.delete_title')}</h3>
    <br />
    <h4 class="gray">{$c->__('post.delete_text')}</h4>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat"
        onclick="Post_ajaxDeleteConfirm('{$to}', '{$node}', '{$id}'); Dialog_ajaxClear()">
        {$c->__('button.delete')}
    </button>
</div>
