{loop="$reactions"}
    <li title="{$value|implodeCsv}"
        {if="in_array($me, $value)"}class="reacted"{/if}
        onclick="Chat_ajaxHttpDaemonSendReaction('{$message->mid}', '{$key}')">
        {autoescape="off"}
            {$key|addEmojis:true}
        {/autoescape}
        {$value|count}
    </li>
{/loop}

{if="!empty($reactions)"}
    <li onclick="Stickers_ajaxReaction('{$message->mid}')" title="{$c->__('message.react')}">
        +<i class="material-icons">mood</i>
    </li>
{/if}