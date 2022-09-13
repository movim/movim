{if="$fingerprints->count() > 0"}
<form>
    <div>
        <ul class="list fill middle">
            <li class="subheader">
                <div>
                    <p>{$c->__('omemo.fingerprints')}</p>
                </div>
            </li>
            {loop="$fingerprints"}
                <li>
                    <span class="control icon {if="$value->self"}green{elseif="$value->built"}blue{else}gray{/if}">
                        <i class="material-icons">fingerprint</i>
                    </span>
                    {if="!$value->self"}
                        <span class="control active icon gray divided" onclick="Account_ajaxDeleteBundleConfirm({$value->bundleid})">
                            <i class="material-icons">delete</i>
                        </span>
                    {/if}
                    <span class="primary">
                        <div class="checkbox">
                            <input
                                type="checkbox"
                                data-identifier="{$value->jid}.{$value->bundleid}"
                                id="accountsessionstate_{$value->bundleid}"
                                name="accountsessionstate_{$value->bundleid}"
                                onchange="Account.toggleFingerprintState(this)"/>
                            <label for="accountsessionstate_{$value->bundleid}"></label>
                        </div>
                    </span>
                    <div>
                        <p class="normal">
                            <span class="fingerprint {if="$value->self"}self{/if}">
                                {$value->fingerprint}
                            </span>
                        </p>
                        {if="isset($value->latest)"}
                            <p>{$c->__('omemo.last_activity')}: {$value->latest|strtotime|prepareDate:true}</p>
                        {/if}
                    </div>
                </li>
                {if="$value->self"}<br /><hr /><br />{/if}
            {/loop}
        </ul>
    </div>
</form>

{/if}