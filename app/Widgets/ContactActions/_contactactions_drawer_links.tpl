<ul class="list thick">
    {loop="$links"}
        {autoescape="off"}
            {$resolved = $value->resolvedUrl->cache}
            {if="$resolved"}
                {$c->prepareEmbedUrl($value)}
            {/if}
        {/autoescape}
    {/loop}
</ul>

{if="$more"}
    <ul class="list middle" onclick="ContactActions.moreLinks(this, '{$jid}', {$page + 1})">
        <hr />
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
