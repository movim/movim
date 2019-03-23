{loop="$reactions"}
    <li title="{$value|implode:','}"
        onclick="Chat_ajaxHttpSendReaction('{$message->mid}', '{$key}')">
        {autoescape="off"}
            {$key|addEmojis}
        {/autoescape}
        {$value|count}
    </li>
{/loop}

{if="!empty($reactions)"}
    <li onclick="Stickers_ajaxReaction('{$message->mid}')">
        +<i class="material-icons">mood</i>
    </li>
{/if}