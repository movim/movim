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
        {$picture = $value->getPicture()}
        {if="$picture != null"}
            <span class="primary icon thumb" style="background-image: url({$picture});"></span>
        {else}
            {$url = $value->getContact()->getPhoto('l')}
            {if="$url"}
                <span class="primary icon thumb" style="background-image: url({$url});">
                </span>
            {else}
                <span class="primary icon thumb color {$value->getContact()->jid|stringToColor}">
                    <i class="zmdi zmdi-account"></i>
                </span>
            {/if}
        {/if}
        <p class="line">
        {if="isset($value->title)"}
            {$value->title}
        {else}
            {$value->node}
        {/if}
        </p>
        <p>
            <a href="{$c->route('contact', $value->getContact()->jid)}">
                <i class="zmdi zmdi-account"></i> {$value->getContact()->getTrueName()}
            </a> –
            {$value->published|strtotime|prepareDate}
        </p>

        <p>
            {$value->contentcleaned|strip_tags}
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
    {if="!filter_var($value->origin, FILTER_VALIDATE_EMAIL)"}
        {$attachments = $value->getAttachments()}
        <li
            class="block condensed"
            data-id="{$value->nodeid}"
            data-server="{$value->origin}"
            data-node="{$value->node}">
            {$picture = $value->getPicture()}
            {if="current(explode('.', $value->origin)) == 'nsfw'"}
                <span class="primary icon thumb color red tiny">
                    +18
                </span>
            {elseif="$picture != null"}
                <span class="primary icon thumb" style="background-image: url({$picture});"></span>
            {else}
                <span class="primary icon thumb color {$value->node|stringToColor}">
                    {$value->node|firstLetterCapitalize}
                </span>
            {/if}
            <p class="line">
            {if="isset($value->title)"}
                {$value->title}
            {else}
                {$value->node}
            {/if}
            </p>
            <p>
                {$value->origin} /
                <a href="{$c->route('group', array($value->origin, $value->node))}">
                    <i class="zmdi zmdi-pages"></i> {$value->node}
                </a> –
                {$value->published|strtotime|prepareDate}
            </p>

            <p>
                {if="current(explode('.', $value->origin)) != 'nsfw'"}
                    {$value->contentcleaned|strip_tags}
                {/if}
            </p>
        </li>
    {/if}
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

    <ul class="list thick flex on_desktop">
        <li class="block">
            <span class="primary icon bubble color blue">
                <i class="zmdi zmdi-share"></i>
            </span>
            <p class="line">{$c->__('hello.share_title')}</p>
            <p>{$c->__('hello.share_text')}</p>
        </li>
        <li class="block">
            <a class="button" href="javascript:(function(){location.href='{$c->route('share', '\'+escape(encodeURIComponent(location.href));')}})();"><i class="zmdi zmdi-share"></i> {$c->__('hello.share_button')}</a>
        </li>
    </ul>
{/if}

