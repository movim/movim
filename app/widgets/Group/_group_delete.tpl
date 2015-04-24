<section>
    <h3>{$c->__('group.delete_title')}</h3>
    <br />
    <h4 class="gray">{$c->__('group.delete_text')}</h4>
    <br />
    <h4 class="gray">{$node}</h4>
</section>
<div class="no_bar">
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.cancel')}
    </a>
    <a 
        name="submit" 
        class="button flat" 
        onclick="Group_ajaxDeleteConfirm('{$server}', '{$node}'); Dialog.clear()">
        {$c->__('button.remove')}
    </a>
</div>
