<section>
    <h3>{$c->__('room.banned_remove')}</h3>
    <br />
    <h4 class="gray">{$jid}</h4>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat"
        onclick="RoomsUtils_ajaxRemoveBannedConfirm('{$room->conference}', '{$jid|echapJS}'); Dialog_ajaxClear()">
        {$c->__('button.submit')}
    </button>
</div>
