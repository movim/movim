{loop="$reactions"}
    <li title="{$value|implode:', '}"
        {if="in_array($me, $value)"}class="reacted"{/if}
        onclick="Chat_ajaxHttpSendReaction('{$message->mid}', '{$key}')">
        {autoescape="off"}
            {$key|addEmojis:true}
        {/autoescape}
        {$value|count}
    </li>
{/loop}

{if="!empty($reactions)"}
    <li onclick="Stickers_ajaxReaction('{$message->mid}')">
        +<i class="material-icons">mood</i>
    </li>
{/if}