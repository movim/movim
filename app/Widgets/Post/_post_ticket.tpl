<li id="{$post->nodeid|cleanupId}"
    class="block ticket {if="$post->embed"}embed{/if} {if="$post->isStory()"}story{/if}"

    {if="$public"}
        {if="$post->isMicroblog()"}
            onclick="MovimUtils.reload('{$c->route('blog', [$post->server, $post->nodeid])}')"
        {else}
            onclick="MovimUtils.reload('{$c->route('community', [$post->server, $post->node, $post->nodeid])}')"
        {/if}
    {else}
        {if="$post->isStory()"}
            onclick="StoriesViewer_ajaxHttpGet({$post->id})"
        {else}
            onclick="MovimUtils.reload('{$c->route('post', [$post->server, $post->node, $post->nodeid])}'); Drawer.clear()"
        {/if}
    {/if}
>
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
        <span class="control icon thumb">
            {if="$post->info"}
                <img src="{$post->info->getPicture(\Movim\ImageSize::M)}">
            {else}
                <img src="{$post->node|avatarPlaceholder}">
            {/if}
        </span>
    {/if}
    <div>
        {if="$post->isBrief()"}
            <p class="line {if="!$post->isStory()"}normal brief two{/if}" title="{$post->title}">
                {autoescape="off"}
                    {$post->title}
                {/autoescape}
            </p>
        {else}
            <p class="line {if="!$post->isStory()"}two{/if}" title="{$post->title}">
                {autoescape="off"}
                    {$post->title}
                {/autoescape}
            </p>
            <p dir="auto">{autoescape="off"}{$post->getSummary()|prepareString}{/autoescape}</p>
        {/if}
        <p class="line">
            {if="$post->contact"}
                <span class="icon bubble tiny">
                    <img src="{$post->contact->getPicture()}">
                </span>
            {/if}
            <a href="#" onclick="MovimUtils.reload('{$c->route('contact', $post->aid)}')">
                {$post->truename}
            </a>

            {if="!$post->isMicroblog()"}
                <a class="node"
                title="{$post->server} / {$post->node}"
                href="#"
                onclick="MovimUtils.reload('{$c->route('community', [$post->server, $post->node])}')">
                {if="$post->contact"}•{/if}
                {$post->node}
                </a>
            {/if}

            <span class="info" title="{$post->published|prepareDate}">
                {$count = $post->pictures->count()}
                {if="$count > 1"}
                    {$count} <i class="material-symbols">collections</i> •
                {/if}

                {$count = $post->user_views_count}
                {if="$count > 2"}
                    {$count} <i class="material-symbols">visibility</i> •
                {/if}

                {$count = $post->likes->count()}
                {if="$count > 0"}
                    {$count} <i class="material-symbols fill">favorite</i>
                {/if}

                {$count = $post->comments->count()}
                {if="$count > 0"}
                    {$count} <i class="material-symbols">chat_bubble_outline</i> •
                {/if}

                {$post->published|prepareDate:true,true}
            </span>
        </p>
    </div>
</li>
