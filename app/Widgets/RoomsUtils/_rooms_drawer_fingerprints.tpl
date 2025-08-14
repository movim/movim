<form>
    <div>
        <ul class="list middle">
            <li class="subheader">
                <div>
                    <p>{$c->__('omemo.fingerprints')}</p>
                </div>
            </li>
            {loop="$fingerprints"}
                <li class="subheader">
                    <div>
                        {if="$contacts->has($key)"}
                            <p>{$contacts->get($key)->truename} <span class="second">{$key}</span></p>
                        {else}
                            <p>{$key}</p>
                        {/if}
                    </div>
                </li>
                {loop="$value"}
                    <li>
                        <span class="primary icon gray" id="sessionicon_{$value->jid}_{$value->bundleid}">
                            <i class="material-symbols">fingerprint</i>
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
                        </div>
                    </li>
                {/loop}
            {/loop}
        </ul>
    </div>
</form>
