<li class="block"
    {if="$post->isMicroblog()"}
        onclick="MovimUtils.redirect('{$c->route('contact', $post->server)}')"
    {else}
        onclick="MovimUtils.redirect('{$c->route('community', [$post->server, $post->node])}')"
    {/if}
    >
    <span class="control icon gray">
        <i class="material-icons">expand_less</i>
    </span>
    {if="$post->isMicroblog()"}
        {if="$post->contact"}
            {$url = $post->contact->getPhoto('m')}
            {if="$url"}
                <span class="primary icon bubble"
                    style="background-image: url({$url});">
                </span>
            {else}
                <span class="primary icon bubble color {$post->contact->jid|stringToColor}">
                    <i class="material-icons">person</i>
                </span>
            {/if}
            <p class="normal line">
                {$post->contact->truename}
            </p>
        {else}
            <span class="primary icon bubble color {$post->server|stringToColor}">
                <i class="material-icons">person</i>
            </span>
            <p class="normal line">
                {$post->server}
            </p>
        {/if}
        <p class="line">
            {$post->server}
        </p>
    {else}
        {if="$info"}
            {$url = $info->getPhoto('m')}

            {if="$url"}
                <span class="primary icon bubble">
                    <img src="{$url}"/>
                </span>
            {else}
                <span class="primary icon bubble color {$info->node|stringToColor}">
                    {$info->node|firstLetterCapitalize}
                </span>
            {/if}
            <p class="line normal">
                {if="$info->name"}
                    {$info->name}
                {else}
                    {$info->node}
                {/if}
            </p>
            {if="$info->description"}
                <p class="line">{$info->description|strip_tags}</p>
            {/if}
        {else}
            <span class="primary icon bubble color {$post->node|stringToColor}">
                {$post->node|firstLetterCapitalize}
            </span>
            <p class="line normal">
                {$post->node}
            </p>
            <p>{$post->server}</p>
        {/if}
    {/if}
</li>