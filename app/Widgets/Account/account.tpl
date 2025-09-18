<div class="tabelem" title="{$c->__('account.title')}" data-mobileicon="account_circle" id="account_widget">
    <div id="account_presences"></div>
    {if="$c->me->hasOMEMO()"}<div id="account_fingerprints"></div>{/if}
    <div id="account_gateways"></div>
    <ul class="list active">
        <li class="subheader">
            <div>
                <p>{$c->__('account.account_management')}</p>
            </div>
        </li>
        <li onclick="Account_ajaxChangePassword()">
            <span class="primary icon">
                <i class="material-symbols">vpn_key</i>
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chevron_right</i>
            </span>
            <div>
                <p class="normal line">{$c->__('account.password_change_title')}</p>
            </div>
        </li>
        <li onclick="Account_ajaxClearAccount()">
            <span class="primary icon orange">
                <i class="material-symbols">eject</i>
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chevron_right</i>
            </span>
            <div>
                <p class="normal line">{$c->__('account.clear')}</p>
            </div>
        </li>
        <li onclick="Account_ajaxRemoveAccount()">
            <span class="primary icon red">
                <i class="material-symbols">delete</i>
            </span>
            <span class="control icon gray">
                <i class="material-symbols">chevron_right</i>
            </span>
            <div>
                <p class="normal line">{$c->__('account.delete')}</p>
            </div>
        </li>
    </ul>
</div>
