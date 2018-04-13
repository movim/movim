<ul class="list column third middle active card">
    {loop="$posts"}
        {$c->prepareTicket($value)}
    {/loop}
</ul>

