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
            {$url = $value->getPhoto('m')}
            {if="$url"}
                <span class="primary icon bubble active
                    {if="$value->locationDistance"} location{/if}
                    {if="!$value->presence || $value->presence->value > 4"}
                        faded
                    {else}
                        status {$value->presence->presencekey}
                    {/if}"
                    style="background-image: url({$url});"
                    onclick="MovimUtils.reload('{$c->route('contact', $value->jid)}')">
                </span>
            {else}
                <span class="primary icon bubble color active {$value->jid|stringToColor}
                    {if="$value->locationDistance"} location{/if}
                    {if="!$value->presence || $value->presence->value > 4"}
                        faded
                    {else}
                        status {$value->presence->presencekey}
                    {/if}"
                    onclick="MovimUtils.reload('{$c->route('contact', $value->jid)}')"
                >
                    <i class="material-icons">person</i>
                </span>
            {/if}

            {if="$value->presences->count() > 0"}
                {loop="$value->presences"}
                    {if="$value->capability && $value->capability->isJingleAudio()"}
                        <span title="{$c->__('button.audio_call')}" class="control icon active gray"
                            onclick="VisioLink.openVisio('{$value->jid|echapJS}');">
                            <i class="material-icons">phone</i>
                        </span>
                    {/if}
                    {if="$value->capability && $value->capability->isJingleVideo()"}
                        <span title="{$c->__('button.video_call')}" class="control icon active gray"
                            onclick="VisioLink.openVisio('{$value->jid|echapJS}', '', true);">
                            <i class="material-icons">videocam</i>
                        </span>
                        {break}
                    {/if}
                {/loop}
            {/if}
            <span class="control icon active gray divided" onclick="Search.chat('{$value->jid|echapJS}')">
                <i class="material-icons">comment</i>
            </span>
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
                            <i class="material-icons">{$value->presence->capability->getDeviceIcon()}</i>
                        </span>
                    {/if}

                    {if="!in_array($value->subscription, ['', 'both'])"}
                        <span class="second">
                            {if="$value->subscription == 'to'"}
                                <i class="material-icons">arrow_upward</i>
                            {elseif="$value->subscription == 'from'"}
                                <i class="material-icons">arrow_downward</i>
                            {else}
                                <i class="material-icons">block</i>
                            {/if}
                        </span>
                    {/if}
                </p>

                {if="$value->presence && $value->presence->seen"}
                    <p>
                        {$c->__('last.title')} {$value->presence->seen|strtotime|prepareDate:true,true}
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
                <i class="material-icons">expand_more</i>
            </span>
            <div>
                <p class="normal line">
                    {$c->__('search.show_complete_roster')}
                    <span class="second">{$contacts->count()} <i class="material-icons">people</i></span>
                </p>
            </div>
        </li>
    {/if}
{else}
    <ul class="list thick">
        <li>
            <span class="primary icon blue">
                <i class="material-icons">help</i>
            </span>
            <div>
                <p>{$c->__('search.no_contacts_title')}</p>
                <p>{$c->__('search.no_contacts_text')}</p>
            </div>
        </li>
    </ul>
{/if}