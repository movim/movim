{if="$conference->unreads_count > 0"}
    <span class="counter">{$conference->unreads_count}</span>
{/if}
{if="$withAvatar == false"}
    {autoescape="off"}
        {$conference->name|firstLetterCapitalize|addEmojis}
    {/autoescape}
{/if}
