<ul class="list column third active card">
    {loop="$posts"}
        {$c->prepareTicket($value)}
    {/loop}
</ul>

