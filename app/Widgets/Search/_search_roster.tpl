{if="$contacts->isNotEmpty()"}
    <li class="subheader">
        <div>
            <p>{$c->__('page.contacts')}</p>
        </div>
    </li>
    {loop="$contacts"}
        <li
            id="{$value->jid|cleanupId}"
            title="{$value->jid}"
            name="{$value->jid|cleanupId}-{if="$value->truename"}{$value->truename|cleanupId}{/if}-{if="$value->group"}{$value->group|cleanupId}{/if}{if="$value->presence"}-{$value->presence->presencetext|cleanupId}{/if}"
            class="{if="$value->presence && $value->presence->value > 4"}faded{/if}"
        >
            <span class="primary icon bubble active
                {if="!$value->presence || $value->presence->value > 4"}
                    faded
                {else}
                    status {$value->presence->presencekey}
                {/if}"
                onclick="MovimUtils.reload('{$c->route('contact', $value->jid)}'); Drawer.clear();">
                <img loading="lazy" src="{$value->getPicture(\Movim\ImageSize::M)}">
            </span>

            {if="$value->presences->count() > 0"}
                {loop="$value->presences"}
                    {if="$value->capability && $value->capability->isJingleAudio()"}
                        <span title="{$c->__('button.audio_call')}" class="control icon active gray"
                            onclick="Visio_ajaxGetLobby('{$value->jid|echapJS}', true); Drawer.clear();">
                            <i class="material-symbols">phone</i>
                        </span>
                    {/if}
                    {if="$value->capability && $value->capability->isJingleVideo()"}
                        <span title="{$c->__('button.video_call')}" class="control icon active gray"
                            onclick="Visio_ajaxGetLobby('{$value->jid|echapJS}', true, true); Drawer.clear();">
                            <i class="material-symbols">videocam</i>
                        </span>
                        {break}
                    {/if}
                {/loop}
            {/if}

            {if="$value->jid != $c->me->id"}
                <span class="control icon active gray divided" onclick="Search.chat('{$value->jid|echapJS}', false)">
                    <i class="material-symbols">comment</i>
                </span>
            {/if}
            <div>
                <p class="normal line">
                    {$value->truename}
                    {if="$value->group"}
                        <span class="tag color {$value->group|stringToColor}">
                            {$value->group}
                        </span>
                    {/if}
                    {if="$value->presence && $value->presence->capability"}
                        <span class="second" title="{$value->presence->capability->name}">
                            <i class="material-symbols">{$value->presence->capability->getDeviceIcon()}</i>
                        </span>
                    {/if}

                    {if="!in_array($value->subscription, ['', 'both'])"}
                        <span class="second">
                            {if="$value->subscription == 'to'"}
                                <i class="material-symbols">arrow_upward</i>
                            {elseif="$value->subscription == 'from'"}
                                <i class="material-symbols">arrow_downward</i>
                            {else}
                                <i class="material-symbols">block</i>
                            {/if}
                        </span>
                    {/if}
                </p>

                {if="$value->presence && $value->presence->seen"}
                    <p>
                        {$c->__('last.title')} {$value->presence->seen|prepareDate:true,true}
                    </p>
                {elseif="$value->presence"}
                    <p>{$value->presence->presencetext}</p>
                {/if}
            </div>
        </li>
    {/loop}

    {if="$contacts->count() > 7"}
        <li class="showall active" onclick="Search.showCompleteRoster(this)">
            <span class="primary icon gray">
                <i class="material-symbols">expand_more</i>
            </span>
            <div>
                <p class="normal line">
                    {$c->__('search.show_complete_roster')}
                    <span class="second">{$contacts->count()} <i class="material-symbols">people</i></span>
                </p>
            </div>
        </li>
    {/if}
{else}
    <ul class="list thick">
        <li>
            <span class="primary icon blue">
                <i class="material-symbols">help</i>
            </span>
            <div>
                <p>{$c->__('search.no_contacts_title')}</p>
                <p>{$c->__('search.no_contacts_text')}</p>
            </div>
        </li>
    </ul>
{/if}
