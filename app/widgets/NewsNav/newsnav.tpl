<ul class="list">
    <li>
        <p class="line">
            <h4 class="gray"></h4>
        </p>
    </li>
</ul>

{if="$c->getView() == 'news'"}
    <ul class="list active middle card shadow">
        <li class="subheader">
            <p>{$c->__('post.blog_last')}</p>
        </li>
        {loop="$blogs"}
            {$attachments = $value->getAttachments()}
            <li class="block" onclick="MovimUtils.redirect('{$c->route('post', [$value->origin, $value->node, $value->nodeid])}')">
                {if="$value->picture"}
                    <span class="primary thumb icon" style="background-image: url('{$value->picture}');"></span>
                {else}
                    <span class="primary thumb color icon color {$value->node|stringToColor}">
                        {$value->node|firstLetterCapitalize}
                    </span>
                {/if}
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
{/if}

<ul class="list active middle card shadow">
    <li class="subheader active">
        {if="$c->getView() == 'news'"}
        <span class="control active icon gray">
            <a href="{$c->route('community')}">
                <i class="zmdi zmdi-chevron-right"></i>
            </a>
        </span>
        {/if}
        <p>{$c->__('page.communities')}</p>
    </li>

    {loop="$posts"}
        <li class="block" onclick="MovimUtils.redirect('{$c->route('post', [$value->origin, $value->node, $value->nodeid])}')">
            {if="$value->picture"}
                <span class="primary thumb icon" style="background-image: url('{$value->picture}');"></span>
            {else}
                <span class="primary thumb color icon color {$value->node|stringToColor}">
                    {$value->node|firstLetterCapitalize}
                </span>
            {/if}
            <p class="line" {if="isset($value->title)"}title="{$value->title}"{/if}>
            {if="isset($value->title)"}
                {$value->title}
            {else}
                {$value->node}
            {/if}
            </p>
            <p dir="auto">{$value->contentcleaned|strip_tags|truncate:140}</p>
            <p>
                <a href="{$c->route('community', [$value->origin, $value->node])}">{$value->node}</a>

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

{if="$c->getView() == 'news' && $c->supported('pubsub')"}
    <ul class="list thick on_desktop card">
        <li class="block">
            <p class="line">{$c->__('hello.share_title')}</p>
            <p class="all">{$c->__('hello.share_text')}</p>
            <p class="center">
            <a class="button" onclick="return false;" href="javascript:(function(){location.href='{$c->route('share', '\'+encodeURIComponent(location.href);')}})();"><i class="zmdi zmdi-share"></i> {$c->__('button.share')}</a></p>
        </li>
    </ul>
{/if}

