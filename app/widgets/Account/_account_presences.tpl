<ul class="list fill middle">
    <li class="subheader">
        <div>
            <p>{$c->__('clients.title_full')}</p>
        </div>
    </li>
    {loop="$presences"}
        {if="$value->capability"}
            <li class="block">
                <span class="primary icon gray status {$value->presencekey}">
                    <i class="material-icons">
                        {$value->capability->getDeviceIcon()}
                    </i>
                </span>
                <div>
                    <p class="normal line">
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