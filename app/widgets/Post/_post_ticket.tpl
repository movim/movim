<li class="block" onclick="MovimUtils.redirect('{$c->route('post', [$post->server, $post->node, $post->nodeid])}')">
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
            </a>
        {/if}

        {if="!$post->isMicroblog()"}
            <a href="{$c->route('community', [$post->server, $post->node])}">{$post->node}</a>
        {/if}

        {$count = $post->countLikes()}
        {if="$count > 0"}
            {$count} <i class="zmdi zmdi-favorite-outline"></i>
        {/if}

        {$count = $post->countComments()}
        {if="$count > 0"}
            {$count} <i class="zmdi zmdi-comment-outline"></i>
        {/if}
        <span class="info">
            {$post->published|strtotime|prepareDate}
        </span>
    </p>
</li>
