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
                    <span class="primary icon gray" id="sessionicon_{$value->jid}_{$value->bundleid}">
                        <i class="material-icons">fingerprint</i>
                    </span>
                    <span class="control">
                        <div class="checkbox">
                            <input
                                type="checkbox"
                                data-identifier="{$value->jid}.{$value->bundleid}"
                                id="sessionstate_{$value->jid|cleanupId}_{$value->bundleid}"
                                name="sessionstate_{$value->jid|cleanupId}_{$value->bundleid}"
                                onchange="ContactActions.toggleFingerprintState(this)"/>
                            <label for="sessionstate_{$value->jid|cleanupId}_{$value->bundleid}"></label>
                        </div>
                    </span>
                    <div>
                        <p class="normal">
                            <span class="fingerprint" title="{$value->bundleid}">
                                {$value->fingerprint}
                            </span>
                        </p>
                        <p class="line">
                           {if="$value->capability"}
                                {$value->capability->name}&nbsp;
                                <i class="material-icons">{$value->capability->getDeviceIcon()}</i>
                            {/if}
                            {if="$value->capability && isset($value->latest)"}
                            &nbsp;-&nbsp;
                            {/if}
                            {if="isset($value->latest)"}
                                {$c->__('omemo.last_activity')}: {$value->latest|strtotime|prepareDate:true}
                            {/if}
                        </p>
                    </div>
                </li>
            {/loop}
        </ul>
    </div>
</form>
