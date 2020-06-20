{if="$conference"}
    {$curl = $conference->getPhoto()}
{/if}

<section class="scroll">
    <header class="big"
        {if="$curl"}
            style="background-image: linear-gradient(to bottom, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.5) 100%), url('{$conference->getPhoto('xxl')}');"
        {/if}
    >
        <ul class="list thick">
            <li>
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
                <div>
                    {if="$conference && $conference->name"}
                        <p class="line" title="{$room}">
                            {$conference->name}
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
    <ul class="list">
        <p class="all">
            {if="$conference->subject"}
                <li>
                    <span class="primary icon gray">
                        <i class="material-icons">short_text</i>
                    </span>
                    <div>
                        <p class="all normal">
                            {autoescape="off"}
                                {$conference->subject|addUrls}
                            {/autoescape}
                        </p>
                    </div>
                </li>
            {/if}
        </p>
        {if="$conference->info && $conference->info->mucpublic"}
            <li>
                <span class="primary icon gray">
                    <i class="material-icons">wifi_tethering</i>
                </span>
                <div>
                    <p class="line">{$c->__('room.public_muc')}</p>
                    <p class="all">{$c->__('room.public_muc_text')}</p>
                </div>
            </li>
        {/if}
        {if="$conference->info && !$conference->info->mucsemianonymous"}
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
    </ul>

    {if="$conference->pictures()->count() > 0"}
        <ul class="list">
            <li class="subheader">
                <div>
                    <p>
                        {$c->__('general.pictures')}
                    </p>
                </div>
            </li>
        </ul>
        <ul class="grid active">
            {loop="$conference->pictures()->take(8)->get()"}
                <li style="background-image: url('{$value->file['uri']|protectPicture}')"
                    onclick="Preview_ajaxShow('{$value->file['uri']}')">
                    <i class="material-icons">visibility</i>
                </li>
            {/loop}
        </ul>
    {/if}

    <ul class="list thin">
        <li class="subheader">
            <div>
                <p>
                    <span class="info">{$list|count}</span>
                    {$c->__('chatrooms.users')}
                </p>
            </div>
        </li>
        {loop="$list"}
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
                        Chat_ajaxGet('{$value->mucjid|echapJS}');
                        Drawer_ajaxClear();">
                        <i class="material-icons">comment</i>
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
</section>