<ul class="list middle">
    {if="$version"}
    <li class="subheader">
        <div>
            <p>{$c->__('account.xmpp_server_info')}</p>
        </div>
    </li>
    <li>
        <span class="primary icon gray">
            <i class="material-symbols">page_info</i>
        </span>
        <div>
            <p>{$session->host}</p>
            <p>
                {$version->name}<span class="second">• {$version->version} {if="$version->os"} — <i class="material-symbols">hard_drive</i> {$version->os}</i>
            {/if}</span>
            </p>
        </div>
    </li>
    {/if}
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
                    <p class="line">
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
