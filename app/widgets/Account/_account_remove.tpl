<section>
    <h3>{$c->__('account.delete_title')}</h3>
    <br />
    <h4 class="gray">{$c->__('account.delete_text')}</h4>
    <br />
    <h4 class="gray">{$c->__('account.delete_text_confirm')}</h4>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat"
        onclick="Account_ajaxRemoveAccountConfirm(); Dialog_ajaxClear()">
        {$c->__('button.delete')}
    </button>
</div>
