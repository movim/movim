<ul class="list flex third middle active card">
    {loop="$posts"}
        {autoescape="off"}
            {$c->prepareTicket($value)}
        {/autoescape}
    {/loop}
</ul>

{if="$page > 0"}
    <hr />

    <ul class="list thick" onclick="Communities.morePosts(this, {$page})">
        <li class="active">
            <span class="primary icon gray">
                <i class="material-icons">expand_more</i>
            </span>
            <div>
                <p class="line normal center">
                    {$c->__('button.more')}
                </p>
            </div>
        </li>
    </ul>
{/if}