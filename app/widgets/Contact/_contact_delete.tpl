<section>
    <h3>{$c->__('delete.title')}</h3>
    <br />
    <h4 class="gray">{$c->__('delete.text')}</h4>
</section>
<div class="no_bar">
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.cancel')}
    </a>
    <a 
        name="submit" 
        class="button flat" 
        onclick="Roster_ajaxDelete('{$jid}'); Dialog.clear()">
        {$c->__('button.delete')}
    </a>
</div>
