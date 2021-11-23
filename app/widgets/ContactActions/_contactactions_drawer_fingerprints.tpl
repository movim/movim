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
                    {$sessionsCount = $value->sessions->count()}

                    <span class="primary icon {if="$sessionsCount > 0 && $value->sessions->pluck('deviceid')->contains($deviceid)"}blue{else}gray{/if}"
                        title="{$c->__('omemo.sessions_built', $sessionsCount)}">
                        <i class="material-icons">fingerprint</i>
                        {if="$sessionsCount > 1"}
                            <span class="counter alt" data-mucreceipts="true">{$sessionsCount}</span>
                        {/if}
                    </span>
                    <span class="control">
                        <div class="checkbox">
                            <input
                                type="checkbox"
                                data-identifier="{$value->jid}.{$value->bundleid}"
                                id="sessionstate_{$value->bundleid}"
                                name="sessionstate_{$value->bundleid}"
                                onchange="ContactActions.toggleFingerprintState(this)"/>
                            <label for="sessionstate_{$value->bundleid}"></label>
                        </div>
                    </span>
                    <div>
                        <p class="normal">
                            <span class="fingerprint" title="{$value->bundleid}">
                                {$value->fingerprint}
                            </span>
                        </p>
                        {if="isset($value->latest)"}
                            <p>{$c->__('omemo.last_message')}: {$value->latest|strtotime|prepareDate:true}</p>
                        {/if}
                    </div>
                </li>
            {/loop}
        </ul>
    </div>
</form>
