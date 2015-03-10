<div class="tabelem" title="{$c->__('account.title')}" id="account_widget" >
    <ul class="middle active divided ">
        <li class="subheader">Change my password</li>
        <li>
            <span class="icon gray">
                <i class="md md-vpn-key"></i>
            </span>
            <form name="password" class="">
                <div>
                    <input type="password" placeholder="Choose a nice password" name="password"/>
                    <label>Password</label>
                </div>
                <div>
                    <input type="password" placeholder="Type your password again" name="password_confirmation"/>
                    <label>Password confirmation</label>
                </div>
                <a onclick="
                        Account_ajaxChangePassword(movim_form_to_json('password'));
                        this.className='button oppose inactive';" class="button color oppose">
                    {$c->__('button.submit')}
                </a>
            </form>
        </li>
        <li class="subheader">Delete my account</li>
        <li class="action" onclick="Account_ajaxRemoveAccount()">
            <span class="icon red">
                <i class="md md-delete"></i>
            </span>
            <div class="action">
                <i class="md md-chevron-right"></i>
            </div>
            <span>Delete your account</span>
        </li>
    </ul>
</div>
