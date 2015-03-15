<section>
    <h3>{$c->__('post.delete_title')}</h3>
    <br />
    <h4 class="gray">{$c->__('post.delete_text')}</h4>
</section>
<div class="no_bar">
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.cancel')}
    </a>
    <a 
        name="submit" 
        class="button flat" 
        onclick="Post_ajaxDeleteConfirm('{$to}', '{$node}', '{$id}'); Dialog.clear()">
        {$c->__('button.delete')}
    </a>
</div>
