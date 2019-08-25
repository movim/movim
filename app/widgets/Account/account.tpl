<div class="tabelem" title="{$c->__('account.title')}" id="account_widget">
    <div id="account_gateways">
        {autoescape="off"}
            {$gateways}
        {/autoescape}
    </div>
    <ul class="list middle active divided ">
        <li class="subheader">
            <p>{$c->__('account.password_change_title')}</p>
        </li>
        <li>
            <span class="primary icon gray">
                <i class="material-icons">vpn_key</i>
            </span>
            <form name="password">
                <div>
                    <input type="password" placeholder="Choose a nice password" name="password"/>
                    <label>{$c->__('account.password')}</label>
                </div>
                <div>
                    <input type="password" placeholder="Type your password again" name="password_confirmation"/>
                    <label>{$c->__('account.password_confirmation')}</label>
                </div>
                <a onclick="
                        Account_ajaxChangePassword(MovimUtils.formToJson('password'));
                        this.className='button oppose inactive';" class="button color oppose">
                    {$c->__('button.save')}
                </a>
            </form>
        </li>
    </ul>
    <ul class="list middle active">
        <li class="subheader">
            <p>{$c->__('account.clear')}</p>
        </li>
        <li onclick="Account_ajaxClearAccount()">
            <span class="primary icon orange">
                <i class="material-icons">eject</i>
            </span>
            <span class="control icon gray">
                <i class="material-icons">chevron_right</i>
            </span>
            <p class="normal line">{$c->__('account.clear')}</p>
        </li>
    </ul>
    <ul class="list middle active">
        <li class="subheader">
            <p>{$c->__('account.delete')}</p>
        </li>
        <li onclick="Account_ajaxRemoveAccount()">
            <span class="primary icon red">
                <i class="material-icons">delete</i>
            </span>
            <span class="control icon gray">
                <i class="material-icons">chevron_right</i>
            </span>
            <p class="normal line">{$c->__('account.delete')}</p>
        </li>
    </ul>
</div>
