{if="!$communities->isEmpty()"}
<ul class="list middle flex third card shadow active">
    <li class="subheader">
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
