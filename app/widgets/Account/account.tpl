<div class="tabelem" title="{$c->__('account.title')}" id="account_widget">
    <ul class="list middle active">
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

    {if="$fingerprints->count() > 0"}
        <ul class="list middle">
            <li class="subheader">
                <div>
                    <p>{$c->__('omemo.fingerprints')}</p>
                </div>
            </li>
            {loop="$fingerprints"}
                <li>
                    <span class="primary icon gray">
                        <i class="material-icons">fingerprint</i>
                    </span>
                    <div>
                        <p class="normal">
                            <span class="fingerprint">
                                {$value->fingerprint}
                            </span>
                        </p>
                    </div>
                </li>
            {/loop}
        </ul>
    {/if}

    <div id="account_gateways">
        {autoescape="off"}
            {$gateways}
        {/autoescape}
    </div>
</div>
