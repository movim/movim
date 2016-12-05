<ul class="list thick">
    <li>
        <p class="line">
            <h4 class="gray">{$c->__('post.blog_last')}</h4>
        </p>
    </li>
</ul>

<ul class="list active">
{loop="$blogs"}
    {$attachments = $value->getAttachments()}
    <li
        class="block condensed"
        data-id="{$value->nodeid}"
        data-server="{$value->origin}"
        data-node="{$value->node}">

        <p class="line" {if="isset($value->title)"}title="{$value->title}"{/if}>
        {if="isset($value->title)"}
            {$value->title}
        {else}
            {$value->node}
        {/if}
        </p>
        <p dir="auto">{$value->contentcleaned|strip_tags|truncate:140}</p>
        <p>
            <a href="{$c->route('contact', $value->getContact()->jid)}">
                <i class="zmdi zmdi-account"></i> {$value->getContact()->getTrueName()}
            </a>
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

{if="$c->supported('pubsub')"}
    <ul class="list active on_desktop middle">
        <a href="{$c->route('blog', array($jid))}" target="_blank">
            <li>
                <span class="primary icon">
                    <i class="zmdi zmdi-portable-wifi"></i>
                </span>
                <span class="control icon">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>
                <p class="normal line">{$c->__('hello.blog_title')}</p>
            </li>
        </a>
    </ul>
{/if}

<ul class="list thick">
    <li>
        <p class="line">
            <h4 class="gray">{$c->__('page.communities')}</h4>
        </p>
    </li>
</ul>
<ul class="list active">
{loop="$posts"}
    <li
        class="block condensed"
        data-id="{$value->nodeid}"
        data-server="{$value->origin}"
        data-node="{$value->node}">

        <p class="line" {if="isset($value->title)"}title="{$value->title}"{/if}>
        {if="isset($value->title)"}
            {$value->title}
        {else}
            {$value->node}
        {/if}
        </p>
        <p dir="auto">{$value->contentcleaned|strip_tags|truncate:140}</p>
        <p>
            {$value->origin} /
            <a href="{$c->route('group', [$value->origin, $value->node])}">
                <i class="zmdi zmdi-pages"></i> {$value->node}
            </a>
            <span class="info">
                {$value->published|strtotime|prepareDate}
            </span>
        </p>
    </li>
{/loop}
</ul>
{if="$c->supported('pubsub')"}
    <ul class="list active middle">
        <a href="{$c->route('group')}">
            <li>
                <span class="primary icon"><i class="zmdi zmdi-pages"></i></span>
                <span class="control icon">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>
                <p class="normal line">{$c->__('post.discover')}</p>
            </li>
        </a>
    </ul>

    <ul class="list thick on_desktop card">
        <li class="block">
            <p class="line">{$c->__('hello.share_title')}</p>
            <p class="all">{$c->__('hello.share_text')}</p>
            <p class="center">
            <a class="button" onclick="return false;" href="javascript:(function(){location.href='{$c->route('share', '\'+encodeURIComponent(location.href);')}})();"><i class="zmdi zmdi-share"></i> {$c->__('button.share')}</a></p>
        </li>
    </ul>
{/if}

