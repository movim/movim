<ul class="active list thick">
    {loop="$links"}
        {autoescape="off"}
            {$resolved = $value->resolvedUrl->cache}
            {if="$resolved"}
                {$c->prepareEmbedUrl($resolved)}
            {/if}
        {/autoescape}
    {/loop}
</ul>

{if="$more"}
    <ul class="list middle" onclick="RoomsUtils.moreLinks(this, '{$room}', {$page + 1})">
        <hr />
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