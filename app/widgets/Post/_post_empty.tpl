<header>
    <ul class="list middle">
        <li>
            <span class="primary icon active gray on_mobile" onclick="MovimTpl.hidePanel()">
                <i class="zmdi zmdi-arrow-back"></i>
            </span>
            <span class="control"></span>
            <p>
                {$c->__('post.hot')}
            </p>
        </li>
    </ul>
</header>

{if="!isset($top)"}
    <ul class="list thick">
        {$a = '1f600'}
        <li>
            <p>{$c->__('hello.enter_title')}</p>
            <p>{$c->__('hello.enter_paragraph')} <img alt=":smiley:" class="emoji" src="{$a|getSmileyPath}"></p>
        </li>
    </ul>
    <ul class="list middle">
        <li>
            <span class="primary icon gray">
                <i class="zmdi zmdi-menu on_mobile"></i>
                <i class="zmdi zmdi-cloud-outline on_desktop"></i>
            </span>
            <p>{$c->__('hello.menu_title')}</p>
            <p>{$c->__('hello.menu_paragraph')}</p>
        </li>
        {if="$me->isEmpty()"}
            <li>
                <span class="primary icon gray">
                    <i class="zmdi zmdi-settings"></i>
                </span>
                <p>{$c->__('hello.profile_title')}</p>
                <p>{$c->__('hello.profile_paragraph')}</p>
            </li>
        {/if}
    </ul>
{/if}

<ul class="list thick">
    <li>
        <span class="primary icon gray">
            <i class="zmdi zmdi-account"></i>
        </span>
        <p>
            <h4 class="gray">{$c->__('post.blog_last')}</h4>
        </p>
    </li>
</ul>

<ul class="list flex card shadow active">
{loop="$blogs"}
    {$attachments = $value->getAttachments()}
    <li
        class="block condensed"
        data-id="{$value->nodeid}"
        data-server="{$value->origin}"
        data-node="{$value->node}">
        {if="$value->picture != null"}
            <span class="icon top" style="background-image: url({$value->picture});"></span>
        {else}
            <span class="icon top color dark">
                <i class="zmdi zmdi-edit"></i>
            </span>
        {/if}

        {$url = $value->getContact()->getPhoto('m')}
        {if="$url"}
            <span class="primary icon bubble" style="background-image: url({$url});">
            </span>
        {else}
            <span class="primary icon bubble color {$value->getContact()->jid|stringToColor}">
                <i class="zmdi zmdi-account"></i>
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
                <i class="zmdi zmdi-account"></i> {$value->getContact()->getTrueName()}
            </a>
            {$count = $value->countComments()}
            {if="$count > 0"}
                {$count} <i class="zmdi zmdi-comment-outline"></i>
            {/if}
            <span class="info">
                {$value->published|strtotime|prepareDate}
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
                <p class="list">{$c->__('hello.blog_title')}</p>
                <p>{$c->__('hello.blog_text')}</p>
            </li>
            <br/>
        </a>
    </ul>
{/if}

<ul class="list thick">
    <li>
        <span class="primary icon gray">
            <i class="zmdi zmdi-pages"></i>
        </span>
        <p>
            <h4 class="gray">{$c->__('post.hot_text')}</h4>
        </p>
    </li>
</ul>
<ul class="list flex card shadow active">
{loop="$posts"}
    <li
        class="block condensed"
        data-id="{$value->nodeid}"
        data-server="{$value->origin}"
        data-node="{$value->node}">
        {if="$value->picture != null"}
            <span class="icon top" style="background-image: url({$value->picture});"></span>
        {else}
            <span class="icon top color dark">
                {$value->node|firstLetterCapitalize}
            </span>
        {/if}

        {if="$value->logo"}
            <span class="primary icon bubble">
                <img src="{$value->getLogo()}">
            </span>
        {else}
            <span class="primary icon bubble color {$value->node|stringToColor}">
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
            {$value->origin} /
            <a href="{$c->route('community', [$value->origin, $value->node])}">
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
        <a href="{$c->route('community')}">
            <li>
                <span class="primary icon"><i class="zmdi zmdi-pages"></i></span>
                <span class="control icon">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>
                <p class="normal line">{$c->__('post.discover')}</p>
            </li>
        </a>
    </ul>

    <ul class="list thick flex on_desktop">
        <li class="block">
            <span class="primary icon bubble color blue">
                <i class="zmdi zmdi-share"></i>
            </span>
            <p class="line">{$c->__('hello.share_title')}</p>
            <p>{$c->__('hello.share_text')}</p>
        </li>
        <li class="block">
            <a class="button" onclick="return false;" href="javascript:(function(){location.href='{$c->route('share', '\'+encodeURIComponent(location.href);')}})();"><i class="zmdi zmdi-share"></i> {$c->__('button.share')}</a>
        </li>
    </ul>
{/if}

