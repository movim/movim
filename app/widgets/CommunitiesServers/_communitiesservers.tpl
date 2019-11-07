{if="$servers->isEmpty()"}
    <ul class="thick">
        <div class="placeholder">
            <i class="material-icons">storage</i>
            <h1>{$c->__('communitiesservers.empty_title')}</h1>
            <h4>{$c->__('communitiesservers.empty_text')}</h4>
        </li>
    </ul>
{else}
    <ul class="list flex third middle active">
        {loop="$servers"}
            {if="!filter_var($value->server, FILTER_VALIDATE_EMAIL)"}
                <li class="block
                    {if="$value->occupants == 0"}faded{/if}"
                    onclick="MovimUtils.redirect('{$c->route('community', $value->server)}')">
                    <span class="primary icon bubble color {$value->server|stringToColor}">
                        {$value->server|firstLetterCapitalize}
                    </span>
                    <p class="line" title="{$value->server} - {$value->name}">
                        {$value->server}
                        <span class="second">{$value->name}</span>
                    </p>
                    <p>{$c->__('communities.counter', $value->occupants)}</p>
                </li>
            {/if}
        {/loop}
    </ul>
{/if}
<ul class="list middle">
    <li class="block large">
        <span class="primary icon">
            <i class="material-icons">search</i>
        </span>
        <form>
            <div>
                <input placeholder="pubsub.server.com" onkeypress="
                    if (event.keyCode == 13) { CommunitiesServers_ajaxDisco(this.value); return false; }" >
                <label>{$c->__('communities.search_server')}</label>
            </div>
        </form>
    </li>
</ul>