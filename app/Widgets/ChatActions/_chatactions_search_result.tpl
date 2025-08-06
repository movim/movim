{if="$messages->isEmpty()"}
    <div class="placeholder">
        <i class="material-symbols">search_off</i>
        <h1>{$c->__('chatactions.search_messages')}</h1>
        <h4>{$c->__('chatactions.search_messages_empty')}</h4>
    </div>
{else}
    <ul class="list active divided" id="message_preview">
        {loop="$messages"}
            {autoescape="off"}
                {$c->prepareMessage($value, true)}
            {/autoescape}
        {/loop}
    </ul>
{/if}