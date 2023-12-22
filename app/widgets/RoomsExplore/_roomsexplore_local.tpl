{if="$rooms->isNotEmpty()"}
    <li class="subheader">
        <div>
            <p>{$c->__('chatrooms.title')}</p>
        </div>
    </li>
    {loop="$rooms"}
        <li title="{$value->server}">
            {if="$vcards->has($value->server)"}
                <span class="primary icon bubble"
                    style="background-image: url({$vcards->get($value->server)->getPicture()});">
                </span>
            {else}
                <span class="primary icon bubble color {$value->name|stringToColor}">
                    {$value->name|firstLetterCapitalize}
                </span>
            {/if}
            {if="$bookmarks->has($value->server)"}
                <span class="control icon gray">
                    <i class="material-symbols">bookmark</i>
                </span>
            {else}
                <span class="control icon gray active divided"
                    onclick="Drawer.clear(); RoomsUtils_ajaxAdd('{$value->server}', '{$value->name}')">
                    <i class="material-symbols">add</i>
                </span>
            {/if}
            <div>
                <p class="line">
                    {$value->name ?? ''}
                    {if="$value->server"}
                        <span class="second">{$value->server}</span>
                    {/if}
                </p>
                <p class="line" title="{$value->description ?? ''}">
                    {if="$value->occupants > 0"}
                        <span title="{$c->__('communitydata.sub', $value->occupants)}">
                            {$value->occupants} <i class="material-symbols">people</i>
                        </span>
                    {/if}
                    {if="$value->occupants > 0 && !empty($value->description)"} · {/if}
                    {$value->description ?? ''}
                </p>
            </div>
        </li>
    {/loop}
{else}
    <div class="placeholder">
        <i class="material-symbols">explore</i>
        <h1>{$c->__('roomsexplore.no_local')}</h1>
    </div>
{/if}