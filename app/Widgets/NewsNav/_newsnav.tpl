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
    <li class="subheader active" onclick="MovimUtils.reload('{$c->route('explore')}')">
        {if="$page == 'news'"}
        <span class="control active icon gray">
            <a href="{$c->route('explore')}">
                <i class="material-symbols">chevron_right</i>
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

{if="$page == 'news' && $c->me->hasPubsub()"}
    <ul class="list thick on_desktop card">
        <li class="block color indigo">
            <i class="main material-symbols">share</i>
            <div>
                <p class="line">{$c->__('hello.share_title')}</p>
                <p class="all">{$c->__('hello.share_text')}</p>
                <p>
                    <a class="button oppose color transparent" onclick="return false;" href="javascript:(function(){location.href='{$c->route('share', '\'+btoa(location.href);')}})();"><i class="material-symbols">share</i> {$c->__('button.share')}</a>
                </p>
            </div>
        </li>
    </ul>
{/if}
