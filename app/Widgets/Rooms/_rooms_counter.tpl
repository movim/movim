<a href="#" onclick="listIconClick(event)" title="{$conference->name}">
    {if="$conference->isFromSpace()"}
        <i class="material-symbols icon gray">tag</i>
    {elseif="$withAvatar == false"}
        {autoescape="off"}
            {$conference->name|firstLetterCapitalize|addEmojis}
        {/autoescape}
    {/if}
    {if="$conference->notify == 2 && $conference->unreads_count > 0"}
        <span class="counter">{$conference->unreads_count}</span>
    {elseif="$conference->quoted_count > 0"}
        <span class="counter notifications">{$conference->quoted_count}</span>
    {elseif="$conference->unreads_count > 0"}
        <span class="counter"><i class="material-symbols">chat_bubble_outline</i></span>
    {/if}
</a>