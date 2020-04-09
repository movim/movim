<ul class="list">
    <li>
        <content>
            <p class="line">
                <h4 class="gray"></h4>
            </p>
        </content>
    </li>
</ul>

{if="$c->getView() == 'news'"}
    <ul class="list active middle card shadow">
        <li class="subheader">
            <content>
                <p>{$c->__('post.blog_last')}</p>
            </content>
        </li>
        {loop="$blogs"}
            {autoescape="off"}
                {$c->prepareTicket($value)}
            {/autoescape}
        {/loop}
    </ul>
{/if}

{if="$posts->isNotEmpty()"}
<ul class="list active middle card shadow">
    <li class="subheader active">
        {if="$c->getView() == 'news'"}
        <span class="control active icon gray">
            <a href="{$c->route('community')}">
                <i class="material-icons">chevron_right</i>
            </a>
        </span>
        {/if}
        <content>
            <p>{$c->__('page.communities')}</p>
        </content>
    </li>

    {loop="$posts"}
        {autoescape="off"}
            {$c->prepareTicket($value)}
        {/autoescape}
    {/loop}
</ul>
{/if}

{if="$c->getView() == 'news' && $c->getUser()->hasPubsub()"}
    <ul class="list thick on_desktop card">
        <li class="block">
            <content>
                <p class="line">{$c->__('hello.share_title')}</p>
                <p class="all">{$c->__('hello.share_text')}</p>
                <p class="center">
                <a class="button" onclick="return false;" href="javascript:(function(){location.href='{$c->route('share', '\'+encodeURIComponent(location.href);')}})();"><i class="material-icons">share</i> {$c->__('button.share')}</a></p>
            </content>
        </li>
    </ul>
{/if}
