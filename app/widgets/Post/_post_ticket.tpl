<li id="{$post->nodeid|cleanupId}"
    class="block"
    onclick="MovimUtils.redirect('{$c->route('post', [$post->server, $post->node, $post->nodeid])}')">
    {if="$post->picture != null"}
        <img class="main"
             src="{$post->picture->href|protectPicture}"
             {if="!empty($post->picture->title)"}
                alt="{$post->picture->title}"
                title="{$post->picture->title}"
             {/if}>
        <span class="control icon thumb color
            {if="$post->contact"}
                {$post->contact->jid|stringToColor}
            {else}
                {$post->node|stringToColor}
            {/if}
        "
            style="background-image: url({$post->picture->href|protectPicture});"
        >
        </span>
    {elseif="!$post->contact"}
        <span class="control icon bubble color {$post->node|stringToColor}"
            {$url = false}
            {if="$post->info"}
                {$url = $post->info->getPhoto('m')}
            {/if}
            {if="$url"}
                style="background-image: url({$url});"
            {/if}
        >
            {if="$url == false"}
                {$post->node|firstLetterCapitalize}
            {/if}
        </span>
    {/if}
    <div>
        <p class="line two" {if="isset($post->title)"}title="{$post->title}"{/if}>
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
                {$url = $post->contact->getPhoto('s')}
                <span class="icon bubble tiny {if="$url == null"}color {$post->contact->jid|stringToColor}{/if}"
                    {if="$url"}
                        style="background-image: url('{$url}');">
                    {else}
                        >
                        {$post->contact->truename|firstLetterCapitalize}
                    {/if}
                </span>
                <a href="{$c->route('contact', $post->contact->jid)}">
                    {$post->contact->truename}
                </a>
            {/if}

            {if="!$post->isMicroblog()"}
                <a class="node"
                   title="{$post->server} / {$post->node}"
                   href="{$c->route('community', [$post->server, $post->node])}">
                   {if="$post->contact"}·{/if}
                   {$post->node}
                </a>
            {/if}

            <span class="info" title="{$post->published|strtotime|prepareDate}">
                {$count = $post->likes->count()}
                {if="$count > 0"}
                    {$count} <i class="material-icons">favorite_border</i> ·
                {/if}

                {$count = $post->comments->count()}
                {if="$count > 0"}
                    {$count} <i class="material-icons">chat_bubble_outline</i> ·
                {/if}

                {$post->published|strtotime|prepareDate:true,true}
            </span>
        </p>
    </div>
</li>
