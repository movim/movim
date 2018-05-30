<li class="block" onclick="MovimUtils.redirect('{$c->route('post', [$post->server, $post->node, $post->nodeid])}')">
    {if="$big && $post->picture != null"}
        <img class="icon thumb" src="{$post->picture->href|protectPicture}">
    {elseif="!$big"}
    <span class="primary icon thumb color
        {if="$post->contact"}
            {$post->contact->jid|stringToColor}
        {else}
            {$post->node|stringToColor}
        {/if}"
    {if="$post->picture != null"}
        style="background-image: url({$post->picture->href|protectPicture});"
    {elseif="$post->contact"}
        {$url = $post->contact->getPhoto('l')}
        {if="$url"}
            style="background-image: url({$url});"
        {/if}
    {/if}
    >
        {if="$post->contact"}
            {$post->contact->truename|firstLetterCapitalize}
        {else}
            {$post->node|firstLetterCapitalize}
        {/if}
    </span>
    {/if}
    <p class="line" {if="isset($post->title)"}title="{$post->title}"{/if}>
        {if="isset($post->title)"}
            {$post->title}
        {else}
            {$post->node}
        {/if}
    </p>
    <p dir="auto">{$post->getSummary()}</p>
    <p>
        {if="$post->contact"}
            <a href="{$c->route('contact', $post->contact->jid)}">
                {$post->contact->truename}
            </a> –
        {/if}

        {if="!$post->isMicroblog()"}
            <a title="{$post->server} / {$post->node}"
               href="{$c->route('community', [$post->server, $post->node])}">
               {$post->node}
            </a>
        {/if}

        {$count = $post->likes()->count()}
        {if="$count > 0"}
            {$count} <i class="zmdi zmdi-favorite-outline"></i>
        {/if}

        {$count = $post->comments()->count()}
        {if="$count > 0"}
            {$count} <i class="zmdi zmdi-comment-outline"></i>
        {/if}
        <span class="info">
            {$post->published|strtotime|prepareDate}
        </span>
    </p>
</li>
