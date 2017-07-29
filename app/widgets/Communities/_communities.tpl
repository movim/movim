<header>
    <ul class="list middle">
        <li>
            {if="$c->supported('pubsub')"}
            <span class="control icon gray active" onclick="MovimUtils.redirect('{$c->route('community', 'subscriptions')}')">
                <i class="zmdi zmdi-settings"></i>
            </span>
            {/if}
            <p class="center">{$c->__('page.communities')}</p>
            <p class="center line">{$c->__('communities.empty_text')}</p>
        </li>
    </ul>
</header>

<ul class="list flex middle active">
    {loop="$communities"}
        <li
            class="block
                {if="$value->subscription == 'subscribed'"}action{/if}
                {if="$value->occupants > 0 || $value->num > 0"}condensed{/if}
                "
            onclick="MovimUtils.redirect('{$c->route('community', [$value->server, $value->node])}')"
            title="{$value->server} - {$value->node}"
        >
            {if="$value->subscription == 'subscribed'"}
                <span class="control icon gray">
                    <i class="zmdi zmdi-bookmark"></i>
                </span>
            {/if}

            {if="$value->logo"}
                <span class="primary icon bubble">
                    <img src="{$value->getLogo(50)}">
                </span>
            {else}
                <span class="primary icon bubble color {$value->node|stringToColor}">
                    {$value->node|firstLetterCapitalize}
                </span>
            {/if}
            <p class="line">
                {if="$value->name"}
                    {$value->name}
                {else}
                    {$value->node}
                {/if}
                <span class="second">
                    {if="$value->description"}
                        {$value->description|strip_tags}
                    {/if}
                </span>
            </p>
            <p>
                {$value->server}
                {if="$value->occupants > 0"}
                    <span title="{$c->__('communitydata.sub', $value->occupants)}">
                        - {$value->occupants} <i class="zmdi zmdi-accounts"></i>
                    </span>
                {/if}
                <span class="info">
                    {$value->published|strtotime|prepareDate:true,true}
                </span>
            </p>
        </li>
    {/loop}
</ul>

<ul class="list flex middle active">
    <li class="subheader block large">
        <p>{$c->__('communities.servers')}</p>
    </li>
    {loop="$servers"}
        {if="!filter_var($value->server, FILTER_VALIDATE_EMAIL)"}
            <li class="block
                {if="empty($value->number)"}faded{/if}"
                onclick="MovimUtils.redirect('{$c->route('community', $value->server)}')">
                <span class="primary icon bubble color {$value->server|stringToColor}">
                    {$value->server|firstLetterCapitalize}
                </span>
                <p class="line" title="{$value->server} - {$value->name}">
                    {$value->server}
                    <span class="second">{$value->name}</span>
                </p>
                <p>{$c->__('communities.counter', (empty($value->number)) ? 0 : $value->number)}</p>
            </li>
        {/if}
    {/loop}
    <li class="block large">
        <span class="primary icon">
            <i class="zmdi zmdi-search-for"></i>
        </span>
        <form>
            <div>
                <input placeholder="pubsub.server.com" onkeypress="
                    if(event.keyCode == 13) { Communities_ajaxDisco(this.value); return false; }" >
                <label>{$c->__('communities.search_server')}</label>
            </div>
        </form>
    </li>
</ul>
