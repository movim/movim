<section>
    <h3>{$c->__('room.group_chat_remove')}</h3>
    <br />
    <h4 class="gray">{$c->__('room.group_chat_remove_text')}</h4>
    <br />
    <h4 class="gray">{$jid}</h4>
</section>
<footer>
    <button onclick="RoomsUtils_ajaxConfigureUser('{$room|echapJS}', '{$jid|echapJS}');" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat color red"
        onclick="RoomsUtils_ajaxRemoveMemberConfirm('{$room|echapJS}', '{$jid|echapJS}'); Dialog_ajaxClear()">
        {$c->__('button.remove')}
    </button>
</footer>
