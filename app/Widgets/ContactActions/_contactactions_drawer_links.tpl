{$resolvedLink = false}
<ul class="list thick">
    {loop="$links"}
        {autoescape="off"}
            {$resolved = $value->resolvedUrl}
            {if="$resolved"}
                {$c->prepareEmbedUrl($value)}
                {$resolvedLink = true}
            {/if}
        {/autoescape}
    {/loop}
</ul>

{if="$resolvedLink == false"}
    <ul class="thick">
        <div class="placeholder">
            <i class="material-symbols">link</i>
            <h1>{$c->__('general.links')}</h1>
        </li>
    </ul>
{/if}

{if="$more"}
    <ul class="list middle" onclick="ContactActions.moreLinks(this, '{$jid}', {$page + 1})">
        <hr />
        <li class="active">
            <span class="primary icon gray">
                <i class="material-symbols">expand_more</i>
            </span>
            <div>
                <p class="line center">
                    {$c->__('button.more')}
                </p>
            </div>
        </li>
    </ul>
{/if}
