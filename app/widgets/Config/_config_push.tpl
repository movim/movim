{if="$pushSubscriptions->count() > 0"}
<form>
    <div>
        <ul class="list middle">
            <li class="subheader">
                <div>
                    <p>{$c->__('config.push_subscriptions')}</p>
                </div>
            </li>
            {loop="$pushSubscriptions"}
                <li>
                    <span class="control icon gray">
                        <i class="material-icons">notifications</i>
                    </span>
                    <div>
                        <p class="normal">
                            {$value->browser}
                        </p>
                        <p>{$value->platform}</p>
                    </div>
                </li>
            {/loop}
        </ul>
    </div>
</form>
{/if}