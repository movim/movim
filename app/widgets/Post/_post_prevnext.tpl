{if="$post->next || $post->previous"}
    <ul class="list card flex active">
        {if="$post->previous"}
            {autoescape="off"}
                {$c->prepareTicket($post->previous)}
            {/autoescape}
        {/if}
        {if="$post->next"}
            {autoescape="off"}
                {$c->prepareTicket($post->next)}
            {/autoescape}
        {/if}
    </ul>
{/if}
