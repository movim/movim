{if="$servers->isEmpty()"}
    <ul class="thick">
        <div class="placeholder">
            <i class="material-symbols">storage</i>
            <h1>{$c->__('communitiesservers.empty_title')}</h1>
            <h4>{$c->__('communitiesservers.empty_text')}</h4>
        </li>
    </ul>
{else}
    <ul class="list flex third card shadow middle active">
        <li class="subheader">
            <div>
                <p>{$c->__('communities.servers')}</p>
            </div>
        </li>
        {loop="$servers"}
            {if="!filter_var($value->server, FILTER_VALIDATE_EMAIL)"}
                <li class="block
                    {if="$value->occupants == 0"}faded{else}color {$value->server|stringToColor}{/if}"
                    onclick="MovimUtils.reload('{$c->route('community', $value->server)}')">
                    <span class="primary icon bubble color transparent">
                        <i class="material-symbols">workspaces</i>
                    </span>
                    <div>
                        <p class="line" title="{$value->server} - {$value->name}">
                            {$value->server}
                        </p>
                        <p class="line">{$c->__('communities.counter', $value->occupants)}<span class="second">• {$value->name}</span></p>
                    </div>
                </li>
            {/if}
        {/loop}
    </ul>
{/if}
{if="!$restrict"}
    <ul class="list middle">
        <li class="block large">
            <span class="primary icon">
                <i class="material-symbols">search</i>
            </span>
            <form>
                <div>
                    <input placeholder="pubsub.server.com" onkeypress="
                        if (event.key == 'Enter') { CommunitiesServers_ajaxDisco(this.value); return false; }" >
                    <label>{$c->__('communities.search_server')}</label>
                </div>
            </form>
        </li>
    </ul>
{/if}
