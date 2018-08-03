<ul class="list column third middle active card">
    {loop="$posts"}
        {autoescape="off"}
            {$c->prepareTicket($value)}
        {/autoescape}
    {/loop}
</ul>
