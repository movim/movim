<section>
    <h3>{$c->__('delete.title')}</h3>
    <br />
    <h4 class="gray">{$c->__('delete.text')}</h4>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat"
        onclick="ContactHeader_ajaxDelete('{$jid|echapJS}'); Dialog_ajaxClear()">
        {$c->__('button.delete')}
    </button>
</div>
