<section>
    <h3>{$c->__('account.password_change_title')}</h3>
    <br />

    <form name="password">
        <div>
            <input type="password" placeholder="Choose a nice password" name="password"/>
            <label>{$c->__('account.password')}</label>
        </div>
        <div>
            <input type="password" placeholder="Type your password again" name="password_confirmation"/>
            <label>{$c->__('account.password_confirmation')}</label>
        </div>
    </form>
</ul>

</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <a id="password_save" onclick="
        Account_ajaxChangePasswordConfirm(MovimUtils.formToJson('password'));
        this.className='button flat inactive';" class="button flat color oppose">
        {$c->__('button.save')}
    </a>
</div>