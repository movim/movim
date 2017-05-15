<section>
    <h3>{$c->__('account.clear')}</h3>
    <br />
    <h4 class="gray">{$c->__('account.clear_text')}</h4>
    <br />
    <h4 class="gray">{$c->__('account.clear_text_confirm')}</h4>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat"
        onclick="Account_ajaxClearAccountConfirm(); Dialog_ajaxClear()">
        {$c->__('button.clear')}
    </button>
</div>
