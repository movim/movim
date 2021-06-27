{if="$fingerprints->count() > 0"}
    <ul class="list middle">
        <li class="subheader">
            <div>
                <p>{$c->__('omemo.fingerprints')}</p>
            </div>
        </li>
        {loop="$fingerprints"}
            <li>
                <span class="primary icon {if="$value->self"}green{else}gray{/if}">
                    <i class="material-icons">fingerprint</i>
                </span>
                <div>
                    <p class="normal">
                        <span class="fingerprint {if="$value->self"}self{/if}">
                            {$value->fingerprint}
                        </span>
                    </p>
                </div>
            </li>
        {/loop}
    </ul>
{/if}