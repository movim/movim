<section>
    <h3>{$c->__('account.delete')}</h3>
    <br />
    <h4 class="gray">{$c->__('account.delete_text')}</h4>
    <br />
    <h4 class="gray"></h4>

    <form name="confirmdelete">
        <div>
            <input name="jid" placeholder="username@server.com" type="text" required>
            <label>{$c->__('account.delete_text_confirm')}</label>
        </div>
    </form>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat color red"
        onclick="Account_ajaxRemoveAccountConfirm(MovimUtils.formToJson('confirmdelete')); Dialog_ajaxClear()">
        {$c->__('button.delete')}
    </button>
</div>
