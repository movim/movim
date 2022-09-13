<div class="tabelem padded_top_bottom" title="{$c->__('account.title')}" data-mobileicon="account_circle" id="account_widget">
    <div id="account_presences"></div>
    <div id="account_fingerprints"></div>
    <div id="account_gateways">
        {autoescape="off"}
            {$gateways}
        {/autoescape}
    </div>
    <ul class="list fill active">
        <li class="subheader">
            <div>
                <p>{$c->__('account.account_management')}</p>
            </div>
        </li>
        <li onclick="Account_ajaxChangePassword()">
            <span class="primary icon">
                <i class="material-icons">vpn_key</i>
            </span>
            <span class="control icon gray">
                <i class="material-icons">chevron_right</i>
            </span>
            <div>
                <p class="normal line">{$c->__('account.password_change_title')}</p>
            </div>
        </li>
        <li onclick="Account_ajaxClearAccount()">
            <span class="primary icon orange">
                <i class="material-icons">eject</i>
            </span>
            <span class="control icon gray">
                <i class="material-icons">chevron_right</i>
            </span>
            <div>
                <p class="normal line">{$c->__('account.clear')}</p>
            </div>
        </li>
        <li onclick="Account_ajaxRemoveAccount()">
            <span class="primary icon red">
                <i class="material-icons">delete</i>
            </span>
            <span class="control icon gray">
                <i class="material-icons">chevron_right</i>
            </span>
            <div>
                <p class="normal line">{$c->__('account.delete')}</p>
            </div>
        </li>
    </ul>
</div>
