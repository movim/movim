<ul class="list middle">
    <li>
        {if="$edit"}
            <span class="primary icon bubble space color gray active edit_avatar" onclick="SpaceInfo_ajaxGetAvatar('{$subscription->server}', '{$subscription->node}')">
                <img src="{$subscription->info->getPicture(placeholder: $subscription->info->name)}">
            </span>
            <span class="control icon gray active" onclick="SpaceInfo_ajaxGetConfig('{$subscription->server}', '{$subscription->node}')">
                <i class="material-symbols">tune</i>
            </span>
        {else}
            <span class="primary icon bubble space">
                <img src="{$subscription->info->getPicture(placeholder: $subscription->info->name)}">
            </span>
        {/if}
        {if="$subscription->info"}
            <span class="control icon gray active" onclick="SpaceInfo_ajaxInvite('{$subscription->server}', '{$subscription->node}')">
                <i class="material-symbols">person_add</i>
            </span>
        {/if}
        <div>
            <p class="line">
                {autoescape="off"}{$subscription->info->name|addEmojis}{/autoescape}
            </p>
            <p class="line two">
                {if="!empty($subscription->info->description)"}
                    {autoescape="off"}{$subscription->info->description|addEmojis}{/autoescape}
                {else}
                    {$subscription->info->server}
                {/if}
            </p>
        </div>
    </li>
</ul>

<ul class="list">
    <li class="subheader">
        {if="$edit"}
            <span class="control icon gray active" onclick="SpaceInfo_ajaxEditMember('{$subscription->server}', '{$subscription->node}')">
                <i class="material-symbols">person_edit</i>
            </span>
            <span title="{$c->__('spaceinfo.add_room_title')}" class="control icon gray active" onclick="SpaceRooms_ajaxAdd('{$subscription->server}', '{$subscription->node}')">
                <i class="material-symbols">add</i>
            </span>
            <span class="control icon gray active" onclick="MovimUtils.reloadThis()">
                <i class="material-symbols">check</i>
            </span>
        {else}
            <span class="control icon gray active" onclick="SpaceInfo_ajaxGetAffiliations('{$subscription->server}', '{$subscription->node}')"
                title="{$c->__('spaceinfo.config_title')}">
                <i class="material-symbols">edit</i>
            </span>
        {/if}
        <div>
            <p class="line">
                <span class="second">
                    {$subscription->spaceAffiliations()->count()}
                    <i class="material-symbols">people</i>
                </span>
                <span class="second notify">
                    {if="$subscription->notify == 'never'"}
                        •
                        <i class="material-symbols">notifications_off</i>
                        {$c->__('room.notify_never')}
                    {elseif="$subscription->notify == 'on-mention'"}
                        •
                        <i class="material-symbols">notification_important</i>
                        {$c->__('room.notify_mentioned')}
                    {elseif="$subscription->notify == 'always'"}
                        •
                        <i class="material-symbols">notifications_active</i>
                        {$c->__('room.notify_always')}
                    {/if}
                </span>
            </p>
        </div>
    </li>
    <li id="spaceinfo_pendings"></li>
</ul>
