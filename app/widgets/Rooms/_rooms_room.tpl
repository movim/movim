<li id="{$conference->conference|cleanupId}"
    data-jid="{$conference->conference}"
    {if="$conference->nick != null"} data-nick="{$conference->nick}" {/if}
    class="room {if="$conference->connected"}connected{/if}"
    title="{$conference->conference}">
    {$url = $conference->getPhoto()}
    {if="$url"}
        <span class="primary icon bubble color small
            {$conference->name|stringToColor}"
            id="{$conference->conference|cleanupId}-rooms-primary"
            style="background-image: url({$url});">
            {autoescape="off"}
                {$c->prepareRoomCounter($conference, true)}
            {/autoescape}
        </span>
    {else}
        <span class="primary icon bubble color small
            {$conference->name|stringToColor}"
            id="{$conference->conference|cleanupId}-rooms-primary">
            {autoescape="off"}
                {$c->prepareRoomCounter($conference, false)}
            {/autoescape}
        </span>
    {/if}

    {$info = $conference->info}

    <div>
        <p class="normal line">
            {$conference->name}
            ·
            {if="$conference->connected"}
                {$count = $conference->presences()->count()}
                <span title="{$c->__('communitydata.sub', $count)}"
                    {if="$conference->connected && $conference->presence->mucrole == 'moderator'"}
                        class="moderator"
                    {/if}>
                    {$count} <i class="material-icons">people</i>
                    {if="$conference->info && $conference->info->mucpublic"}
                        <i class="material-icons" title="{$c->__('room.public_muc_text')}">wifi_tethering</i>
                    {/if}
                    {if="$conference->info && !$conference->info->mucsemianonymous"}
                        <i class="material-icons" title="{$c->__('room.nonanonymous_muc_text')}">face</i>
                    {/if}
                </span> ·
            {elseif="isset($info) && $info->occupants > 0"}
                <span title="{$c->__('communitydata.sub', $info->occupants)}"
                    {if="$conference->connected && $conference->presence->mucrole == 'moderator'"}
                        class="moderator"
                    {/if}>
                    {$info->occupants} <i class="material-icons">people</i>
                    {if="$conference->info && $conference->info->mucpublic"}
                        <i class="material-icons" title="{$c->__('room.public_muc_text')}">wifi_tethering</i>
                    {/if}
                    {if="$conference->info && !$conference->info->mucsemianonymous"}
                        <i class="material-icons" title="{$c->__('room.nonanonymous_muc_text')}">face</i>
                    {/if}
                </span> ·
            {/if}
            {if="$conference->connected"}
                {if="isset($info) && $info->description"}
                    {$info->description}
                {else}
                    {$conference->conference}
                {/if}
            {else}
                <span class="second">{$conference->conference}</span>
            {/if}
        </p>
    </div>
    <span class="control icon active gray" onclick="event.stopPropagation(); RoomsUtils_ajaxRemove('{$conference->conference|echapJS}');">
        <i class="material-icons">delete</i>
    </span>
    <span class="control icon active gray" onclick="event.stopPropagation(); RoomsUtils_ajaxAdd('{$conference->conference|echapJS}');">
        <i class="material-icons">edit</i>
    </span>
</li>