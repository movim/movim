<ul class="list flex third middle active card">
    <li class="block" onclick="MovimUtils.redirect('{$c->route('explore', 'servers')}')">
        <span class="primary icon">
            <i class="material-icons">view_lists</i>
        </span>
        <span class="control icon">
            <i class="material-icons">chevron_right</i>
        </span>
        <div>
            <p>{$c->__('communities.servers')}</p>
            <p>{$c->__('communities.servers_text')}</p>
        </div>
    </li>
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