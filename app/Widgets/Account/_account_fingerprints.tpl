{if="$fingerprints->count() > 0"}
<form>
    <div>
        <ul class="list middle">
            <li class="subheader">
                <div>
                    <p>{$c->__('omemo.fingerprints')}</p>
                </div>
            </li>
            {loop="$fingerprints"}
                <li>
                    <span class="primary icon {if="$value->self"}green{else}gray{/if}">
                        <i class="material-symbols">fingerprint</i>
                    </span>
                    {if="!$value->self"}
                        <span class="control active icon gray" onclick="Account.deleteBundle({$value->bundleid})">
                            <i class="material-symbols">delete</i>
                        </span>
                        <span class="control">
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
                    {/if}
                    <div>
                        <p class="normal">
                            <span class="fingerprint {if="$value->self"}self{/if}">
                                {$value->fingerprint}
                            </span>
                        </p>
                        {if="isset($value->latest)"}
                            <p>{$c->__('omemo.last_activity')}: {$value->latest|prepareDate:true}</p>
                        {/if}
                    </div>
                </li>
                {if="$value->self && $fingerprints->count() > 1"}<br /><hr /><br />{/if}
            {/loop}
        </ul>
    </div>
</form>

{/if}
