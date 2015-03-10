<section>
    <h3>{$c->__('account.delete_title')}</h3>
    <br />
    <h4 class="gray">{$c->__('account.delete_text')}</h4>
    <br />
    <h4 class="gray">{$c->__('account.delete_text_confirm')}</h4>
</section>
<div class="no_bar">
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.cancel')}
    </a>
    <a 
        name="submit" 
        class="button flat" 
        onclick="Account_ajaxRemoveAccountConfirm(); Dialog.clear()">
        {$c->__('button.delete')}
    </a>
</div>
