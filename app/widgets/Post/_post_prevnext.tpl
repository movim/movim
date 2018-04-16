{if="$post->next || $post->previous"}
    <ul class="list card flex active">
        {if="$post->previous"}
            {$c->prepareTicket($post->previous)}
        {/if}
        {if="$post->next"}
            {$c->prepareTicket($post->next)}
        {/if}
    </ul>
{/if}
