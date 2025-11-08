{$affiliation = null}
{loop="$presences"}
    {if="$affiliation != $value->affiliationTxt"}
        <li class="subheader">
            {if="$value->mucaffiliation == 'owner'"}
                <span class="control icon tiny yellow" title="{$c->__('room.affiliation_owner')}">
                    <i class="material-symbols fill">star</i>
                </span>
            {elseif="$value->mucaffiliation == 'admin'"}
                <span class="control icon tiny gray" title="{$c->__('room.affiliation_owner')}">
                    <i class="material-symbols fill">star</i>
                </span>
            {/if}
            <div>
                <p>
                    {$value->affiliationTxt}
                </p>
            </div>
        </li>
    {/if}
    <li class="{if="$value->last > 60"} inactive{/if}"
        title="{$value->resource}">

        <span class="primary icon bubble small status active {$value->presencekey}"
            {if="$value->mucjid && $compact"}
                onclick="ChatActions_ajaxGetContact('{$value->mucjid}')"
            {/if}
            >
            <img loading="lazy" src="{$value->conferencePicture}">
        </span>
        {if="$compact == false"}
            {if="$value->mucjid != $c->me"}
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
        {/if}
        <div {if="$compact"}onclick="Chat.quoteMUC('{$value->resource}', true);"{/if}>
            <p class="line normal">
                {if="$value->mucjid && strpos($value->mucjid, '/') == false"}
                    {if="$value->mucjid == $c->me || $compact"}
                        {$value->resource}
                    {else}
                        <a href="#" onclick="MovimUtils.reload('{$c->route('contact', $value->mucjid)}')">{$value->resource}</a>
                    {/if}
                {else}
                    {$value->resource}
                {/if}
                {if="$value->capability"}
                    <span class="second" title="{$value->capability->name}">
                        <i class="material-symbols">{$value->capability->getDeviceIcon()}</i>
                    </span>
                {/if}
                {if="$value->mucrole == 'visitor'"}
                    <span class="second gray" title="{$c->__('room.visitor')}">
                        <i class="material-symbols">voice_selection_off</i>
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
    {$affiliation = $value->affiliationTxt}
{/loop}

{if="$more"}
    <li id="room_presences_more" class="active" onclick="RoomsUtils_ajaxAppendPresences('{$conference->conference|echapJS}', true, {$page})">
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
