<header>
    <ul class="list middle">
        <li>
            <span id="menu" class="primary on_mobile icon active" onclick="MovimTpl.toggleMenu()"><i class="zmdi zmdi-menu"></i></span>
            <span class="primary on_desktop icon gray"><i class="zmdi zmdi-home"></i></span>
            <p class="center">{$c->__('page.home')}</p>
        </li>
    </ul>
</header>

<div id="hello_widget" class="divided">
    {if="!isset($top) || !isset($news)"}
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
    <ul class="list flex active middle">
        <li class="subheader block large">
            <p>{$c->__('chat.frequent')}</p>
        </li>
        {if="empty($top)"}
            <li>
                <span class="primary icon gray">
                    <i class="zmdi zmdi-info-outline"></i>
                </span>
                <p class="normal">{$c->__('chats.empty_title')}</p>
            </li>
        {/if}
        {loop="$top"}
            <li tabindex="{$key+1}" class="block"
                onclick="Hello_ajaxChat('{$value->jid}')">
                {$url = $value->getPhoto('s')}
                {if="$url"}
                    <span
                        class="primary icon bubble
                        {if="$value->value"}
                            status {$presencestxt[$value->value]}
                        {/if}">
                        <img src="{$url}">
                    </span>
                {else}
                    <span
                        class="primary icon bubble color {$value->jid|stringToColor}
                        {if="$value->value"}
                            status {$presencestxt[$value->value]}
                        {/if}">
                        <i class="zmdi zmdi-account"></i>
                    </span>
                {/if}

                <p>{$value->getTrueName()}</p>
                <p>
                    {if="isset($value->status)"}
                        {$value->status}
                    {else}
                        {$value->jid}
                    {/if}
                </p>
            </li>
        {/loop}
        <a class="block large" href="{$c->route('chat')}">
            <li>
                <span class="primary icon">
                    <i class="zmdi zmdi-comments"></i>
                </span>
                <span class="control icon">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>
                <p class="normal">{$c->__('hello.chat')}</p>
            </li>
        </a>
    </ul>
    {if="$c->supported('pubsub')"}
        <ul id="news" class="list card shadow flex active">
            {if="empty($news)"}
                <li>
                    <span class="control icon gray">
                        <i class="zmdi zmdi-info-outline"></i>
                    </span>
                    <p>{$c->__('menu.empty_title')}</p>
                </li>
            {/if}
            {loop="$news"}
                {$attachements = $value->getAttachements()}
                <li class="block "
                    data-id="{$value->nodeid}"
                    {if="$value->title != null"}
                        title="{$value->title|strip_tags}"
                    {else}
                        title="{$c->__('hello.contact_post')}"
                    {/if}
                    onclick="movim_reload('{$c->route('news', $value->nodeid)}')"
                >
                    {$picture = $value->getPicture()}
                    {if="current(explode('.', $value->origin)) == 'nsfw'"}
                        <span class="primary icon thumb color red tiny">
                            +18
                        </span>
                    {elseif="$picture != null"}
                        <span class="primary icon thumb" style="background-image: url({$picture});"></span>
                    {elseif="$value->node == 'urn:xmpp:microblog:0'"}
                        {$url = $value->getContact()->getPhoto('l')}
                        {if="$url"}
                            <span class="primary icon thumb" style="background-image: url({$url});">
                            </span>
                        {else}
                            <span class="primary icon thumb color {$value->getContact()->jid|stringToColor}">
                                <i class="zmdi zmdi-account"></i>
                            </span>
                        {/if}
                    {else}
                        <span class="primary icon thumb color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
                    {/if}
                    {if="$value->privacy"}
                        <span class="control icon gray" title="{$c->__('menu.public')}">
                            <i class="zmdi zmdi-portable-wifi"></i>
                        </span>
                    {/if}

                    {if="$value->title != null"}
                        <p class="line">{$value->title}</p>
                    {else}
                        <p class="line">{$c->__('hello.contact_post')}</p>
                    {/if}
                    <p>
                        {if="$value->node == 'urn:xmpp:microblog:0'"}
                            <a href="{$c->route('contact', $value->getContact()->jid)}">
                                <i class="zmdi zmdi-account"></i> {$value->getContact()->getTrueName()}
                            </a> –
                        {else}
                            {$value->origin} /
                            <a href="{$c->route('group', array($value->origin, $value->node))}">
                                <i class="zmdi zmdi-pages"></i> {$value->node}
                            </a> –
                        {/if}
                        {$value->published|strtotime|prepareDate}
                    </p>
                    <p>{$value->contentcleaned|strip_tags}</p>

                </li>
            {/loop}
            <a href="{$c->route('news')}">
                <li>
                    <span class="primary icon">
                        <i class="zmdi zmdi-receipt"></i>
                    </span>
                    <span class="control icon">
                        <i class="zmdi zmdi-chevron-right"></i>
                    </span>
                    <p class="normal line">{$c->__('hello.news_page')}</p>
                </li>
            </a>
        </ul>
        <br />
        <ul class="list active on_desktop">
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
        <ul class="list thick flex on_desktop">
            <li class="block">
                <span class="primary icon bubble color blue">
                    <i class="zmdi zmdi-share"></i>
                </span>
                <p class="line">{$c->__('hello.share_title')}</p>
                <p>{$c->__('hello.share_text')}</p>
            </li>
            <li class="block">
                <a class="button" href="javascript:(function(){location.href='{$c->route('share', '\'+escape(encodeURIComponent(location.href));')}})();">
                    <i class="zmdi zmdi-share"></i> {$c->__('hello.share_button')}
                </a>
            </li>
        </ul>
    {/if}
</div>
