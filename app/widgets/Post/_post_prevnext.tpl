{if="$post->next || $post->previous"}
    <ul class="list card flex active middle">
        {if="$post->previous"}
            {autoescape="off"}
                {$c->prepareTicket($post->previous)}
            {/autoescape}
        {else}
            {autoescape="off"}
                {$c->preparePreviousNextBack($post)}
            {/autoescape}
        {/if}
        {if="$post->next"}
            {autoescape="off"}
                {$c->prepareTicket($post->next)}
            {/autoescape}
        {else}
            {autoescape="off"}
                {$c->preparePreviousNextBack($post)}
            {/autoescape}
        {/if}
    </ul>
{/if}
