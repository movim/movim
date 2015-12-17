<div class="tabelem" title="{$c->__('account.title')}" id="account_widget">
    {if="isset($gateway)"}
    <ul class="list active middle divided">
        <li class="subheader">
            <p>{$c->__('account.gateway_title')}</p>
        </li>
        {loop="$gateway"}
        <li onclick="Account_ajaxGetRegistration('{$value->node}')">
            <span class="primary icon">
                <i class="zmdi zmdi-swap"></i>
            </span>
            <span class="control icon gray">
                <i class="zmdi zmdi-chevron-right"></i>
            </span>
            <p>{$value->name}</p>
            <p>{$value->node}</p>
        </li>
        {/loop}
    </ul>
    {/if}
    <ul class="list middle active divided ">
        <li class="subheader">
            <p>{$c->__('account.password_change_title')}</p>
        </li>
        <li>
            <span class="primary icon gray">
                <i class="zmdi zmdi-key"></i>
            </span>
            <form name="password" class="">
                <div>
                    <input type="password" placeholder="Choose a nice password" name="password"/>
                    <label>{$c->__('account.password')}</label>
                </div>
                <div>
                    <input type="password" placeholder="Type your password again" name="password_confirmation"/>
                    <label>{$c->__('account.password_confirmation')}</label>
                </div>
                <a onclick="
                        Account_ajaxChangePassword(movim_form_to_json('password'));
                        this.className='button oppose inactive';" class="button color oppose">
                    {$c->__('button.submit')}
                </a>
            </form>
        </li>
        <li class="subheader">
            <p>{$c->__('account.delete_title')}</p>
        </li>
        <li onclick="Account_ajaxRemoveAccount()">
            <span class="primary icon red">
                <i class="zmdi zmdi-delete"></i>
            </span>
            <span class="control icon gray">
                <i class="zmdi zmdi-chevron-right"></i>
            </span>
            <p class="normal">{$c->__('account.delete')}</p>
        </li>
    </ul>
</div>
