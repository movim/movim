<div class="tabelem" title="{$c->__('account.title')}" id="account_widget">
    {if="isset($gateway)"}
    <ul class="active middle divided">
        <li class="subheader">{$c->__('account.gateway_title')}</li>
        {loop="$gateway"}
        <li class="condensed action" onclick="Account_ajaxGetRegistration('{$value->node}')">
            <div class="action">
                <i class="zmdi zmdi-chevron-right"></i>
            </div>
            <span class="icon">
                <i class="zmdi zmdi-swap"></i>
            </span>
            <span>{$value->name}</span>
            <p>{$value->node}</p>
        </li>
        {/loop}
    </ul>
    {/if}
    <ul class="middle active divided ">
        <li class="subheader">{$c->__('account.password_change_title')}</li>
        <li>
            <span class="icon gray">
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
        <li class="subheader">{$c->__('account.delete_title')}</li>
        <li class="action" onclick="Account_ajaxRemoveAccount()">
            <span class="icon red">
                <i class="zmdi zmdi-delete"></i>
            </span>
            <div class="action">
                <i class="zmdi zmdi-chevron-right"></i>
            </div>
            <span>{$c->__('account.delete')}</span>
        </li>
    </ul>
</div>
