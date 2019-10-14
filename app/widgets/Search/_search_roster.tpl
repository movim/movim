{if="$contacts->isNotEmpty()"}
    <li class="subheader"><p>{$c->__('page.contacts')}</p></li>
    {loop="$contacts"}
        <li
            id="{$value->jid|cleanupId}"
            title="{$value->jid}"
            name="{$value->jid|cleanupId}-{$value->truename|cleanupId}-{$value->group|cleanupId}"
            class="{if="$value->presence && $value->presence->value > 4"}faded{/if}"
        >
            {$url = $value->getPhoto('m')}
            {if="$url"}
                <span class="primary icon bubble
                    {if="!$value->presence || $value->presence->value > 4"}
                        disabled
                    {else}
                        status {$value->presence->presencekey}
                    {/if}"
                    style="background-image: url({$url});">
                </span>
            {else}
                <span class="primary icon bubble color {$value->jid|stringToColor}
                    {if="!$value->presence || $value->presence->value > 4"}
                        disabled
                    {else}
                        status {$value->presence->presencekey}
                    {/if}"
                >
                    <i class="material-icons">person</i>
                </span>
            {/if}
            <span class="control icon active gray" onclick="MovimUtils.reload('{$c->route('contact', $value->jid)}')">
                <i class="material-icons">person</i>
            </span>
            <span class="control icon active gray" onclick="Search.chat('{$value->jid}')">
                <i class="material-icons">comment</i>
            </span>
            {if="$value->presence && $value->presence->capability && $value->presence->capability->isJingle()"}
                <span title="{$c->__('button.call')}" class="control icon active gray"
                        onclick="VisioLink.openVisio('{$value->presence->jid . '/' . $value->presence->resource}');">
                    <i class="material-icons">phone</i>
                </span>
            {/if}
            <p class="normal line">
                {$value->truename}
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
            {if="$value->group"}
            <p>
                <span class="tag color {$value->group|stringToColor}">
                    {$value->group}
                </span>
            </p>
            {/if}
        </li>
    {/loop}

    {if="$contacts->count() > 7"}
        <br />
        <li class="showall active" onclick="Search.showCompleteRoster(this)">
            <span class="primary icon gray">
                <i class="material-icons">expand_more</i>
            </span>
            <p class="normal line">
                {$c->__('search.show_complete_roster')}
                <span class="second">{$contacts->count()} <i class="material-icons">people</i></span>
            </p>
        </li>
    {/if}
{else}
    <ul class="list thick">
        <li>
            <span class="primary icon blue">
                <i class="material-icons">help</i>
            </span>
            <p>{$c->__('search.no_contacts_title')}</p>
            <p>{$c->__('search.no_contacts_text')}</p>
        </li>
    </ul>
{/if}