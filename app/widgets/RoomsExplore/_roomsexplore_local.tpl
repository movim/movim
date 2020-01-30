{if="$rooms->isNotEmpty()"}
    <li class="subheader">
        <p>{$c->__('chatrooms.title')}</p>
    </li>
    {loop="$rooms"}
        <li title="{$value->server}">
            {if="$vcards->has($value->server)"}
                {$url = $vcards->get($value->server)->getPhoto()}
            {else}
                {$url = null}
            {/if}

            {if="$url"}
                <span class="primary icon bubble color {$value->name|stringToColor}"
                    style="background-image: url({$url});">
                </span>
            {else}
                <span class="primary icon bubble color {$value->name|stringToColor}">
                    {$value->name|firstLetterCapitalize}
                </span>
            {/if}
            {if="$bookmarks->has($value->server)"}
                <span class="control icon gray">
                    <i class="material-icons">bookmark</i>
                </span>
            {else}
                <span class="control icon gray active divided"
                    onclick="Drawer.clear(); Rooms_ajaxAdd('{$value->server}', '{$value->name}')">
                    <i class="material-icons">add</i>
                </span>
            {/if}

            <p class="line">{$value->name}
                <span class="second">{$value->server}</span>
            </p>
            <p class="line" title="{$value->description}">
                {if="$value->occupants > 0"}
                    <span title="{$c->__('communitydata.sub', $value->occupants)}">
                        {$value->occupants} <i class="material-icons">people</i>
                    </span>
                {/if}
                {if="$value->occupants > 0 && !empty($value->description)"} · {/if}
                {$value->description}
            </p>
        </li>
    {/loop}
{else}
    <div class="placeholder">
        <i class="material-icons">explore</i>
        <h1>{$c->__('roomsexplore.no_local')}</h1>
    </div>
{/if}