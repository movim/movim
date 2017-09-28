<ul class="list flex active">
    <li class="subheader">
        <p>{$c->__('explore.explore')}</p>
    </li>
{loop="$users"}
    <li class="block" title="{$value->jid}" onclick="MovimUtils.redirect('{$c->route('contact', $value->jid)}')">
        {$url = $value->getPhoto('s')}
        {if="$url"}
            <span class="primary icon bubble
            {if="$value->value"}
                status {$presencestxt[$value->value]}
            {/if}
            " style="background-image: url({$url});">
            </span>
        {else}
            <span class="primary icon bubble color {$value->jid|stringToColor}
            {if="$value->value"}
                status {$presencestxt[$value->value]}
            {/if}
            ">
                <i class="zmdi zmdi-account"></i>
            </span>
        {/if}

        <p class="normal line">
            {$value->getTrueName()}
            {if="!empty($value->description)"}
                <span class="second" title="{$value->description|strip_tags}">
                    {$value->description|strip_tags|truncate:80}
                </span>
            {/if}
        </p>
    </li>
{/loop}
</ul>

<ul class="list active middle card shadow">
    <li class="subheader">
        <p>{$c->__('post.blog_last')}</p>
    </li>
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
