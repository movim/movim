{if="$withAvatar == false"}
    {autoescape="off"}
        {$conference->name|firstLetterCapitalize|addEmojis}
    {/autoescape}
{/if}
{if="$conference->quoted_count > 0"}
    <span class="counter">{$conference->quoted_count}</span>
{elseif="$conference->unreads_count > 0"}
    <span class="counter"><i class="material-icons">chat_bubble_outline</i></span>
{/if}