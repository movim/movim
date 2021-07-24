{if="$blogs && $blogs->isNotEmpty()"}
    <ul class="list active middle card shadow">
        <li class="subheader">
            <div>
                <p>{$c->__('post.blog_last')}</p>
            </div>
        </li>
        {loop="$blogs"}
            {autoescape="off"}
                {$c->prepareTicket($value)}
            {/autoescape}
        {/loop}
    </ul>
{/if}

{if="$posts && $posts->isNotEmpty()"}
<ul class="list active middle card shadow">
    <li class="subheader active" onclick="MovimUtils.redirect('{$c->route('explore')}')">
        {if="$page == 'news'"}
        <span class="control active icon gray">
            <a href="{$c->route('explore')}">
                <i class="material-icons">chevron_right</i>
            </a>
        </span>
        {/if}
        <div>
            <p>{$c->__('page.explore')}</p>
        </div>
    </li>

    {loop="$posts"}
        {autoescape="off"}
            {$c->prepareTicket($value)}
        {/autoescape}
    {/loop}
</ul>
{/if}

{if="$page == 'news' && $c->getUser()->hasPubsub()"}
    <ul class="list thick on_desktop card">
        <li class="block">
            <div>
                <p class="line">{$c->__('hello.share_title')}</p>
                <p class="all">{$c->__('hello.share_text')}</p>
                <p class="center">
                <a class="button" onclick="return false;" href="javascript:(function(){location.href='{$c->route('share', '\'+encodeURIComponent(location.href);')}})();"><i class="material-icons">share</i> {$c->__('button.share')}</a></p>
            </div>
        </li>
    </ul>
{/if}
