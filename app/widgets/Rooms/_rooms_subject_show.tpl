<section class="scroll">
    <ul class="list thick">
        <li>
            {if="$conference"}
                {$curl = $conference->getPhoto()}
            {/if}

            {if="$curl"}
                <span class="primary icon bubble color active {$conference->name|stringToColor}"
                    style="background-image: url({$curl});">
                </span>
            {else}
                <span class="primary icon bubble color active {$conference->name|stringToColor}">
                    {autoescape="off"}
                        {$conference->name|firstLetterCapitalize|addEmojis}
                    {/autoescape}
                </span>
            {/if}
            {if="$conference && $conference->name"}
                <p class="line" title="{$room}">
                    {$conference->name}
                </p>
            {else}
                <p class="line">
                    {$room}
                </p>
            {/if}
            <p class="all">
                {if="$conference->subject"}
                    {autoescape="off"}
                        {$conference->subject|addUrls}
                    {/autoescape}
                {else}
                    {$room}
                {/if}
            </p>
        </li>
    </ul>
    <ul class="list middle">
        {if="$conference->info && $conference->info->mucpublic"}
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">wifi_tethering</i>
                </span>
                <p class="line">{$c->__('room.public_muc')}</p>
                <p class="all">{$c->__('room.public_muc_text')}</p>
            </li>
        {/if}
        {if="$conference->info && !$conference->info->mucsemianonymous"}
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">face</i>
                </span>
                <p class="line">{$c->__('room.nonanonymous_muc')}</p>
                <p class="all">{$c->__('room.nonanonymous_muc_text')}</p>
            </li>
        {/if}
    </ul>

    <br />
    <hr />

    <ul class="list">
        <li class="subheader">
            <p>
                <span class="info">{$list|count}</span>
                {$c->__('chatrooms.users')}
            </p>
        </li>
        {loop="$list"}
            <li class="{if="$value->last > 60"} inactive{/if}"
                title="{$value->resource}">

                {if="$url = $value->conferencePicture"}
                    <span class="primary icon bubble status {$value->presencekey}">
                        <img src="{$url}">
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->resource|stringToColor} status {$value->presencekey}">
                        <i class="material-icons">people</i>
                    </span>
                {/if}
                {if="$value->mucjid != $me"}
                <span class="control icon active gray divided" onclick="
                    Chats_ajaxOpen('{$value->mucjid|echapJS}');
                    Chat_ajaxGet('{$value->mucjid|echapJS}');
                    Dialog_ajaxClear();">
                    <i class="material-icons">comment</i>
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
                <p class="line normal">
                    {if="$value->mucjid && strpos($value->mucjid, '/') == false && !$c->supported('anonymous')"}
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
                {if="$value->status"}
                    <p class="line" title="{$value->status}">{$value->status}</p>
                {/if}
            </li>
        {/loop}
    </ul>
</section>