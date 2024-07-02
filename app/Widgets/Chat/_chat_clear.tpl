<section>
    <h3>{$c->__('chat.clear_history')}</h3>
    <br />
    <h4 class="gray">{$c->__('chat.clear_history_text', $count)}</h4>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat"
        onclick="Chat_ajaxClearHistoryConfirm('{$jid|echapJS}'); Dialog_ajaxClear()">
        {$c->__('button.delete')}
    </button>
</div>
