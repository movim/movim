{if="$conference"}
    {$curl = $conference->getPhoto()}
{/if}

<section class="scroll">
    <header class="big"
        {if="$curl"}
            style="background-image: linear-gradient(to bottom, rgba(23,23,23,0.8) 0%, rgba(23,23,23,0.5) 100%), url('{$conference->getPhoto('xxl')}');"
        {/if}
    >
        <ul class="list thick">
            <li>
                {if="$curl"}
                    <span class="primary icon bubble active"
                        style="background-image: url({$curl});">
                    </span>
                {else}
                    <span class="primary icon bubble color active {$conference->name|stringToColor}">
                        {autoescape="off"}
                            {$conference->name|firstLetterCapitalize|addEmojis}
                        {/autoescape}
                    </span>
                {/if}
                <span title="{$c->__('chatroom.config')}"
                      class="control icon active"
                      onclick="RoomsUtils_ajaxAdd('{$room|echapJS}'); Drawer.clear()">
                    <i class="material-icons">edit</i>
                </span>
                <span title="{$c->__('button.delete')}"
                      class="control icon active"
                      onclick="RoomsUtils_ajaxRemove('{$room|echapJS}'); Drawer.clear()">
                    <i class="material-icons">delete</i>
                </span>

                <div>
                    {if="$conference && $conference->title"}
                        <p class="line" title="{$room}">
                            {$conference->title}
                        </p>
                        <p class="line">{$room}</p>
                    {else}
                        <p class="line">
                            {$room}
                        </p>
                    {/if}
                </div>
            </li>
        </ul>
    </header>

    <ul class="list middle">
        {if="$conference->isGroupChat()"}
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">people_alt</i>
                </span>
                <div>
                    <a class="button flat oppose" >


                    </a>
                    <p class="line">{$c->__('room.group_chat')}</p>
                    <p class="all">{$c->__('room.group_chat_text')}</p>
                </div>
            </li>
        {else}
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">wifi_tethering</i>
                </span>
                <div>
                    <p class="line">{$c->__('room.channel')}</p>
                    <p class="all">{$c->__('room.channel_text')}</p>
                </div>
            </li>
        {/if}
    </ul>
    <ul class="list">
        {if="$conference->subject"}
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">short_text</i>
                </span>
                <div>
                    <p class="line">{$c->__('page.about')}</p>
                    <p class="all">
                        {autoescape="off"}
                            {$conference->subject|addUrls}
                        {/autoescape}
                    </p>
                </div>
            </li>
        {/if}

        {if="$conference->info && $conference->info->mucpublic"}
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">explore</i>
                </span>
                <div>
                    <p class="line">{$c->__('room.public_muc')}</p>
                    <p class="all">{$c->__('room.public_muc_text')}</p>
                </div>
            </li>
        {/if}
        {if="!$conference->isGroupChat() && $conference->info && !$conference->info->mucsemianonymous"}
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">face</i>
                </span>
                <div>
                    <p class="line">{$c->__('room.nonanonymous_muc')}</p>
                    <p class="all">{$c->__('room.nonanonymous_muc_text')}</p>
                </div>
            </li>
        {/if}

        <li class="subheader">
            <div>
                <p>{$c->__('room.notify_title')}</p>
            </div>
        </li>

        {if="$conference->notify == 0"}
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">notifications_off</i>
                </span>
                <span class="control icon gray active"
                      onclick="RoomsUtils_ajaxAdd('{$room|echapJS}'); Drawer.clear()">
                    <i class="material-icons">settings</i>
                </span>
                <div>
                    <p class="line">{$c->__('room.notify_title')}</p>
                    <p class="line">{$c->__('room.notify_never')}</p>
                </div>
            </li>
        {elseif="$conference->notify == 2"}
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">notifications_active</i>
                </span>
                <span class="control icon gray active"
                      onclick="RoomsUtils_ajaxAdd('{$room|echapJS}'); Drawer.clear()">
                    <i class="material-icons">settings</i>
                </span>
                <div>
                    <p class="line">{$c->__('room.notify_title')}</p>
                    <p class="line">{$c->__('room.notify_always')}</p>
                </div>
            </li>
        {else}
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">notifications</i>
                </span>
                <span class="control icon gray active"
                      onclick="RoomsUtils_ajaxAdd('{$room|echapJS}'); Drawer.clear()">
                    <i class="material-icons">settings</i>
                </span>
                <div>
                    <p class="line">{$c->__('room.notify_title')}</p>
                    <p class="line">{$c->__('room.notify_quoted')}</p>
                </div>
            </li>
        {/if}
    </ul>

    <br />
    <ul class="tabs" id="navtabs"></ul>

    {if="$conference->isGroupChat()"}
        <div class="tabelem" title="{$c->__('room.group_chat_members')}" id="room_members">
            <ul class="list">
                <li class="active" onclick="RoomsUtils_ajaxAskInvite('{$conference->conference|echapJS}'); Drawer.clear();">
                    <span class="primary icon gray">
                        <i class="material-icons">person_add</i>
                    </span>
                    <span class="control icon gray">
                        <i class="material-icons">chevron_right</i>
                    </span>
                    <div>
                        <p class="line normal">
                            {$c->__('room.group_chat_add')}
                        </p>
                    </div>
                </li>
            </ul>
            <ul class="list thin">
                {loop="$members"}
                    {$presence = $presences->where('mucjid', $value->jid)->first()}

                    <li title="{$value->truename}">
                        {if="$value->contact && $url = $value->contact->getPhoto('s')"}
                            <span class="primary icon bubble small status {if="$presence"}{$presence->presencekey}{/if}">
                                <img src="{$url}">
                            </span>
                        {else}
                            <span class="primary icon bubble small color {$value->jid|stringToColor} status {if="$presence"}{$presence->presencekey}{/if}">
                                <i class="material-icons">people</i>
                            </span>
                        {/if}
                        {if="$value->affiliation == 'owner'"}
                            <span class="control icon yellow" title="{$c->__('rooms.owner')}">
                                <i class="material-icons">star</i>
                            </span>
                        {elseif="$value->affiliation == 'admin'"}
                            <span class="control icon gray" title="{$c->__('rooms.admin')}">
                                <i class="material-icons">star</i>
                            </span>
                        {/if}
                        {if="$value->jid != $me"}
                            <span class="control icon active gray divided" onclick="
                                Chats_ajaxOpen('{$value->jid|echapJS}');
                                Chat.get('{$value->jid|echapJS}');
                                Drawer_ajaxClear();">
                                <i class="material-icons">comment</i>
                            </span>
                        {/if}
                        {if="$conference->presence && ($conference->presence->mucrole == 'moderator' || $conference->presence->mucaffiliation == 'owner')"}
                            <span class="control icon active gray divided" onclick="
                                RoomsUtils_ajaxChangeAffiliation('{$conference->conference|echapJS}', '{$value->jid|echapJS}');
                                Drawer_ajaxClear();">
                                <i class="material-icons">manage_accounts</i>
                            </span>
                        {/if}
                        <div>
                            <p class="line normal">
                                {if="$value->jid == $me"}
                                    {$value->truename}
                                {else}
                                    <a href="{$c->route('contact', $value->jid)}">{$value->truename}</a>
                                {/if}
                            </p>
                            {if="$presence"}
                                <p>{$presence->resource}</p>
                            {/if}
                        </div>
                    </li>
                {/loop}
            </ul>
        </div>
    {else}
        <div class="tabelem" title="{$c->__('room.channel_users')}" id="room_users">
            <ul class="list">
                <li class="active" onclick="RoomsUtils_ajaxAskInvite('{$conference->conference|echapJS}'); Drawer.clear();">
                    <span class="primary icon gray">
                        <i class="material-icons">person_add</i>
                    </span>
                    <span class="control icon gray">
                        <i class="material-icons">chevron_right</i>
                    </span>
                    <div>
                        <p class="line normal">
                            {$c->__('room.invite')}
                        </p>
                    </div>
                </li>
            </ul>
            <ul class="list thin">
                {loop="$presences"}
                    <li class="{if="$value->last > 60"} inactive{/if}"
                        title="{$value->resource}">

                        {if="$url = $value->conferencePicture"}
                            <span class="primary icon bubble small status {$value->presencekey}">
                                <img src="{$url}">
                            </span>
                        {else}
                            <span class="primary icon bubble small color {$value->resource|stringToColor} status {$value->presencekey}">
                                <i class="material-icons">people</i>
                            </span>
                        {/if}
                        {if="$value->mucaffiliation == 'owner'"}
                            <span class="control icon yellow" title="{$c->__('rooms.owner')}">
                                <i class="material-icons">star</i>
                            </span>
                        {elseif="$value->mucaffiliation == 'admin'"}
                            <span class="control icon gray" title="{$c->__('rooms.admin')}">
                                <i class="material-icons">star</i>
                            </span>
                        {/if}
                        {if="$value->mucrole == 'visitor'"}
                            <span class="control icon gray" title="{$c->__('rooms.visitor')}">
                                <i class="material-icons">speaker_notes_off</i>
                            </span>
                        {/if}
                        {if="$value->mucjid != $me"}
                            <span class="control icon active gray divided" onclick="
                                Chats_ajaxOpen('{$value->mucjid|echapJS}');
                                Chat.get('{$value->mucjid|echapJS}');
                                Drawer_ajaxClear();">
                                <i class="material-icons">comment</i>
                            </span>
                        {/if}
                        {if="$conference->presence && ($conference->presence->mucrole == 'moderator' || $conference->presence->mucaffiliation == 'owner')"}
                            <span class="control icon active gray divided" onclick="
                                RoomsUtils_ajaxChangeAffiliation('{$conference->conference|echapJS}', '{$value->mucjid|echapJS}');
                                Drawer_ajaxClear();">
                                <i class="material-icons">manage_accounts</i>
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
                                        <i class="material-icons">{$value->capability->getDeviceIcon()}</i>
                                    </span>
                                {/if}
                            </p>
                            {if="$value->seen"}
                                <p class="line">
                                    {$c->__('last.title')} {$value->seen|strtotime|prepareDate:true,true}
                                </p>
                            {elseif="$value->status"}
                                <p class="line" title="{$value->status}">{$value->status}</p>
                            {/if}
                        </div>
                    </li>
                {/loop}
            </ul>
        </div>
    {/if}

    {if="$banned->count() > 0"}
        <div class="tabelem" title="{$c->__('chatrooms.banned')}" id="room_banned">
            <ul class="list">
                <li class="active" onclick="RoomsUtils_ajaxAddBanned('{$conference->conference|echapJS}'); Drawer.clear();">
                    <span class="primary icon gray">
                        <i class="material-icons">person_add</i>
                    </span>
                    <span class="control icon gray">
                        <i class="material-icons">chevron_right</i>
                    </span>
                    <div>
                        <p class="line normal">
                            {$c->__('room.banned_add')}
                        </p>
                    </div>
                </li>
            </ul>
            <ul class="list thin">
                {loop="$banned"}
                    <li title="{$value->truename}">
                        {if="$value->contact && $url = $value->contact->getPhoto('s')"}
                            <span class="primary icon bubble small">
                                <img src="{$url}">
                            </span>
                        {else}
                            <span class="primary icon bubble small color {$value->jid|stringToColor}">
                                <i class="material-icons">people</i>
                            </span>
                        {/if}
                        <span class="control icon gray active"
                              onclick="RoomsUtils_ajaxRemoveBanned('{$conference->conference|echapJS}', '{$value->jid|echapJS}'); Drawer.clear();"
                              title="{$c->__('room.banned_remove')}">
                            <i class="material-icons">close</i>
                        </span>
                        <span class="control icon active gray divided" onclick="
                            Chats_ajaxOpen('{$value->jid|echapJS}');
                            Chat.get('{$value->jid|echapJS}');
                            Drawer_ajaxClear();">
                            <i class="material-icons">comment</i>
                        </span>
                        <div>
                            <p class="line normal">
                                {if="$value->jid == $me"}
                                    {$value->truename}
                                {else}
                                    <a href="{$c->route('contact', $value->jid)}">{$value->truename}</a>
                                {/if}
                            </p>
                            <p class="line">{$value->jid}</p>
                        </div>
                    </li>
                {/loop}
            </ul>
        </div>
    {/if}

    {if="$conference->pictures()->count() > 0"}
        <div class="tabelem spin" title="{$c->__('general.pictures')}" id="room_pictures"></div>
    {/if}

    {if="$conference->links()->count() > 0"}
        <div class="tabelem spin" title="{$c->__('general.links')}" id="room_links"></div>
    {/if}

    {if="$hasfingerprints"}
        <div class="tabelem spin" title="{$c->__('omemo.fingerprints_title')}" id="room_omemo_fingerprints"></div>
    {/if}
</section>