{if="!$communities->isEmpty()"}
<ul class="list middle flex third fill card shadow active padded_top_bottom">
    <li class="subheader block large">
        <div>
            <p>{$c->__('communitiesinteresting.about')}</p>
        </div>
    </li>
    {loop="$communities"}
        {autoescape="off"}
            {$c->prepareTicket($value)}
        {/autoescape}
    {/loop}
</ul>
{/if}
