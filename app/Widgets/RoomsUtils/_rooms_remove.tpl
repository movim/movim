<section>
    <h3>{$c->__('chatrooms.remove_title')}</h3>
    <br />
    <h4 class="gray">{$c->__('chatrooms.remove_text')}</h4>
    <br />
    <h4 class="gray">{$room}</h4>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat"
        onclick="Rooms_ajaxExit('{$room|echapJS}'); RoomsUtils_ajaxRemoveConfirm('{$room|echapJS}'); Dialog_ajaxClear()">
        {$c->__('button.remove')}
    </button>
</div>
