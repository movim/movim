<section>
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
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
</div>