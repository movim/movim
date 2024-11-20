<section>
    <h3>{$c->__('delete.title')}</h3>
    <br />
    <h4 class="gray">{$c->__('delete.text')}</h4>
    <br />
    <h4 class="gray">{$jid}</h4>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat"
        onclick="Notifications_ajaxDelete('{$jid|echapJS}'); Dialog_ajaxClear()">
        {$c->__('button.delete')}
    </button>
</footer>
