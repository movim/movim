<ul class="list flex third middle active card shadow">
    {if="$type == 'all' && ($page == 1 || $posts->count() < $limit)"}
        <li class="block color" onclick="MovimUtils.reload('{$c->route('explore', 'servers')}')">
            <i class="material-symbols main">view_cozy</i>
            <span class="primary icon bubble color transparent">
                <i class="material-symbols">view_agenda</i>
            </span>
            <div>
                <p>{$c->__('communities.servers')}</p>
                <p>{$c->__('communities.servers_text')}</p>
            </div>
        </li>
    {/if}

    {if="$posts->count() == 0"}
        <div class="placeholder">
            <i class="material-symbols fill">article</i>
            <h4>{$c->__('chat.new_title')}</h4>
        </div>
    {else}
        {loop="$posts"}
            {autoescape="off"}
                {$c->prepareTicket($value)}
            {/autoescape}
        {/loop}
    {/if}
</ul>

{if="$page > 0"}
    <ul class="list thick" onclick="Communities.morePosts(this, {$page}, '{$type}')">
        <li class="active">
            <span class="primary icon gray">
                <i class="material-symbols">expand_more</i>
            </span>
            <div>
                <p class="line normal center">
                    {$c->__('button.more')}
                </p>
            </div>
        </li>
    </ul>
{/if}
