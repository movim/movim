<ul class="list middle column half active card">
    {loop="$blogs"}
        {$attachments = $value->getAttachments()}
        <li class="block" onclick="MovimUtils.redirect('{$c->route('post', [$value->origin, $value->node, $value->nodeid])}')">
            <span class="primary icon thumb color {$value->getContact()->jid|stringToColor}"
            {$picture = $value->getPicture()}
            {if="$picture != null"}
                style="background-image: url({$picture});"
            {else}
                {$url = $value->getContact()->getPhoto('l')}
                {if="$url"}
                    style="background-image: url({$url});"
                {/if}
            {/if}
            >
                {$value->getContact()->getTrueName()|firstLetterCapitalize}
            </span>
            <p class="line" {if="isset($value->title)"}title="{$value->title}"{/if}>
            {if="isset($value->title)"}
                {$value->title}
            {else}
                {$value->node}
            {/if}
            </p>
            <p dir="auto">{$value->getSummary()}</p>
            <p>
                <a href="{$c->route('contact', $value->getContact()->jid)}">
                    {$value->getContact()->getTrueName()}
                </a>

                {$count = $value->countLikes()}
                {if="$count > 0"}
                    {$count} <i class="zmdi zmdi-favorite-outline"></i>
                {/if}

                {$count = $value->countComments()}
                {if="$count > 0"}
                    {$count} <i class="zmdi zmdi-comment-outline"></i>
                {/if}
                <span class="info">
                    {$value->published|strtotime|prepareDate:true,true}
                </span>
            </p>
        </li>
    {/loop}
</ul>
