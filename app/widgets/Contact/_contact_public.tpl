<ul class="list flex active middle">
{loop="$users"}
    <li class="block" title="{$value->jid}" style="background-image: url();" onclick="Contact_ajaxGetContact('{$value->jid}', {if="$page"}{$page}{else}0{/if});">
        {$url = $value->getPhoto('l')}
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

        <p class="normal">
            {$value->getTrueName()}
        </p>

        <p>
            {$value->description|strip_tags|truncate:80}
            {if="empty($value->description)"}-{/if}
        </p>
    </li>
{/loop}
</ul>
<ul class="list">
{if="$pages"}
    <li>
        <p class="center">
            {loop="$pages"}
                {if="$key == 0 || $key == count($pages)-1  || $key == $page || $key == $page+1 || $key == $page-1"}
                <a onclick="Contact_ajaxPublic({$key});" class="button flat {if="$key == $page"}on{/if}">{$key+1}</a>
                {elseif="$key == $page-2 || $key == $page+2"}
                    …
                {/if}
            {/loop}
        </p>
    </li>
{/if}
</ul>

<ul class="list active middle card flex shadow">
        <li class="subheader block large">
            <p>{$c->__('post.blog_last')}</p>
        </li>
{loop="$blogs"}
    {$attachments = $value->getAttachments()}
    <li class="block" onclick="MovimUtils.redirect('{$c->route('post', [$value->origin, $value->node, $value->nodeid])}')">
        {$picture = $value->getPicture()}
        {if="$picture != null"}
            <span class="primary icon thumb color white" style="background-image: url({$picture});">
            </span>
        {else}
            {$url = $value->getContact()->getPhoto('l')}
            {if="$url"}
                <span class="primary icon thumb color white" style="background-image: url({$url});">
                </span>
            {else}
                <span class="primary icon thumb color {$value->getContact()->jid|stringToColor}">
                    {$value->getContact()->getTrueName()|firstLetterCapitalize}
                </span>
            {/if}
        {/if}
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
