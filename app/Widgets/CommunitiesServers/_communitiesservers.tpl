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
                    <div>
                        <p class="line" title="{$value->server} - {$value->name}">
                            <span class="info">{$c->__('communities.counter', $value->occupants)}</span>
                            {$value->name}
                        </p>
                        <p class="line">{$value->server}</p>
                    </div>
                </li>
            {/if}
        {/loop}
    </ul>
{/if}
