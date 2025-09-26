{loop="$presences"}
    <li class="{if="$value->last > 60"} inactive{/if}"
        title="{$value->resource}">

        <span class="primary icon bubble small status {$value->presencekey}">
            <img loading="lazy" src="{$value->conferencePicture}">
        </span>
        {if="$value->mucaffiliation == 'owner'"}
            <span class="control icon yellow" title="{$c->__('rooms.owner')}">
                <i class="material-symbols fill">star</i>
            </span>
        {elseif="$value->mucaffiliation == 'admin'"}
            <span class="control icon gray" title="{$c->__('rooms.admin')}">
                <i class="material-symbols fill">star</i>
            </span>
        {/if}
        {if="$value->mucrole == 'visitor'"}
            <span class="control icon gray" title="{$c->__('rooms.visitor')}">
                <i class="material-symbols">voice_selection_off</i>
            </span>
        {/if}
        {if="$value->mucjid != $me"}
            <span class="control icon active gray divided" onclick="
                Chats_ajaxOpen('{$value->mucjid|echapJS}', true);
                Drawer.clear();">
                <i class="material-symbols">comment</i>
            </span>
        {/if}
        {if="$conference->presence && ($conference->presence->mucrole == 'moderator' || $conference->presence->mucaffiliation == 'owner')"}
            <span class="control icon active gray divided" onclick="
                RoomsUtils_ajaxConfigureUser('{$conference->conference|echapJS}', '{$value->mucjid|echapJS}');
                Drawer.clear();">
                <i class="material-symbols">manage_accounts</i>
            </span>
        {/if}
        <div>
            <p class="line normal">
                {if="$value->mucjid && strpos($value->mucjid, '/') == false"}
                    {if="$value->mucjid == $me"}
                        {$value->resource}
                    {else}
                        <a href="{$c->route('contact', $value->mucjid)}">{$value->resource}</a>
                    {/if}
                {else}
                    {$value->resource}
                {/if}
                {if="$value->capability"}
                    <span class="second" title="{$value->capability->name}">
                        <i class="material-symbols">{$value->capability->getDeviceIcon()}</i>
                    </span>
                {/if}
            </p>
            {if="$value->seen"}
                <p class="line">
                    {$c->__('last.title')} {$value->seen|prepareDate:true,true}
                </p>
            {elseif="$value->status"}
                <p class="line" title="{$value->status}">{$value->status}</p>
            {/if}
        </div>
    </li>
{/loop}

{if="$more"}
    <li id="room_presences_more" class="active" onclick="RoomsUtils_ajaxAppendPresences('{$conference->conference|echapJS}', {$page}, true)">
        <span class="primary icon gray">
            <i class="material-symbols">expand_more</i>
        </span>
        <div>
            <p class="line normal center">
                {$c->__('button.more')}
            </p>
        </div>
    </li>
{/if}
