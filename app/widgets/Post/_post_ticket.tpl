<li class="block" onclick="MovimUtils.redirect('{$c->route('post', [$post->server, $post->node, $post->nodeid])}')">
    {if="$post->picture != null"}
        <img class="main" src="{$post->picture->href|protectPicture}">
        <span class="primary icon thumb color
            {if="$post->contact"}
                {$post->contact->jid|stringToColor}
            {else}
                {$post->node|stringToColor}
            {/if}
        "
            style="background-image: url({$post->picture->href|protectPicture});"
        >
            {if="$post->contact"}
                {$post->contact->truename|firstLetterCapitalize}
            {else}
                {$post->node|firstLetterCapitalize}
            {/if}
        </span>
    {else}
        <span class="primary icon bubble color
            {if="$post->contact"}
                {$post->contact->jid|stringToColor}
            {else}
                {$post->node|stringToColor}
            {/if}"
            {$url = false}
            {if="$post->contact"}
                {$url = $post->contact->getPhoto('l')}
                {if="$url"}
                    style="background-image: url({$url});"
                {/if}
            {/if}
        >
            {if="$url == false"}
                {if="$post->contact"}
                    {$post->contact->truename|firstLetterCapitalize}
                {else}
                    {$post->node|firstLetterCapitalize}
                {/if}
            {/if}
        </span>
    {/if}
    <div>
        <p class="line" {if="isset($post->title)"}title="{$post->title}"{/if}>
            {if="isset($post->title)"}
                {autoescape="off"}
                    {$post->title|prepareString}
                {/autoescape}
            {else}
                {$post->node}
            {/if}
        </p>
        <p dir="auto">{autoescape="off"}{$post->getSummary()|prepareString}{/autoescape}</p>
        <p>
            {if="$post->contact"}
                <a href="{$c->route('contact', $post->contact->jid)}">
                    {$post->contact->truename}
                </a>
            {/if}

            {if="!$post->isMicroblog()"}
                {if="$post->contact"}·{/if}
                <a title="{$post->server} / {$post->node}"
                   href="{$c->route('community', [$post->server, $post->node])}">
                   {$post->node}
                </a>
            {/if}

            {$count = $post->likes->count()}
            {if="$count > 0"}
                {$count} <i class="material-icons">favorite_border</i>
            {/if}

            {$count = $post->comments->count()}
            {if="$count > 0"}
                {$count} <i class="material-icons">chat_bubble_outline</i>
            {/if}
            <span class="info">
                {$post->published|strtotime|prepareDate}
            </span>
        </p>
    </div>
</li>
