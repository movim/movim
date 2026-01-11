<ul class="list middle">
    <li class="subheader">
        <div>
            <p>{$c->__('clients.title_full')}</p>
        </div>
    </li>
    {loop="$presences"}
        {if="$value->capability"}
            <li class="block">
                <span class="primary icon gray status {$value->presencekey}">
                    <i class="material-symbols">
                        {$value->capability->getDeviceIcon()}
                    </i>
                </span>
                <div>
                    <p class="normal line">
                        {if="$value->resource == $session->resource"}
                        <span class="info">{$c->prepareDate($session->created_at)} - {$session->timezone}</span>
                        {/if}
                        {$value->capability->name}
                        <span class="second">{$value->resource}</span>
                    </p>
                    {if="$value->capability->identities()->first() && isset($clienttype[$value->capability->identities()->first()->type])"}
                        <p class="line">
                            {$clienttype[$value->capability->identities()->first()->type]}
                        </p>
                    {/if}
                </div>
            </li>
        {/if}
    {/loop}
</ul>
