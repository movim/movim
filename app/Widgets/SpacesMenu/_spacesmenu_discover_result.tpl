<ul class="list thick flex card">
    <li class="block large">
        <span class="primary icon bubble space">
            <img src="{$icon}">
        </span>
        <div>
            <p class="line">
                <span class="info">{$space->occupants} <i class="material-symbols">people</i></span>
                {autoescape="off"}{$space->name|addEmojis}{/autoescape}
            </p>
            {if="$space->description"}
                <p>{autoescape="off"}{$space->description|addEmojis}{/autoescape}</p>
            {/if}

            {if="$space->pubsubaccessmodel == 'authorize'"}
                <p>{$c->__('spaceinfo.invite_text')}</p>
            {/if}
        </div>
    </li>
</ul>