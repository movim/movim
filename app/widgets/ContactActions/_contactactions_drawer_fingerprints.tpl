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
            <div>
                <p class="normal">
                    <span class="fingerprint" title="{$value->bundleid}">
                        {$value->fingerprint}
                    </span>
                </p>
                {$latestMessage = $value->getLatestMessage()}
                {if="$latestMessage"}
                    <p class="line">{$c->__('omemo.last_message')}: {$latestMessage|strtotime|prepareTime}</p>
                {/if}

            </div>
        </li>
    {/loop}
</ul>