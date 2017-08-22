<ul class="list flex third middle active">
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
                    if(event.keyCode == 13) { CommunitiesServers_ajaxDisco(this.value); return false; }" >
                <label>{$c->__('communities.search_server')}</label>
            </div>
        </form>
    </li>
</ul>
