<div id="hello_widget" class="divided">
    {if="!isset($top) || !isset($news)"}
        <ul class="simple thick">
            {$a = '1f600'}
            <li>
                <h2>{$c->__('hello.enter_title')}</h2>
                <p>{$c->__('hello.enter_paragraph')} <img alt=":smiley:" class="emoji" src="{$a|getSmileyPath:false}"></p>
            </li>
        </ul>
        <ul class="middle">
            <li class="condensed">
                <span class="icon gray">
                    <i class="zmdi zmdi-menu on_mobile"></i>
                    <i class="zmdi zmdi-cloud-outline on_desktop"></i>
                </span>
                <span>{$c->__('hello.menu_title')}</span>
                <p>{$c->__('hello.menu_paragraph')}</p>
            </li>
        </ul>
    {/if}
    <ul class="flex active middle">
        <li class="subheader block large">{$c->__('chat.frequent')}</li>
        {if="empty($top)"}
            <li>
                <span class="icon gray">
                    <i class="zmdi zmdi-info-outline"></i>
                </span>
                <span>{$c->__('chats.empty_title')}</span>
            </li>
        {/if}
        {loop="$top"}
            <li tabindex="{$key+1}" class="block action {if="$value->status"}condensed{/if}"
                onclick="Hello_ajaxChat('{$value->jid}')">
                {$url = $value->getPhoto('s')}
                {if="$url"}
                    <span
                        class="icon bubble
                        {if="$value->value"}
                            status {$presencestxt[$value->value]}
                        {/if}">
                        <img src="{$url}">
                    </span>
                {else}
                    <span
                        class="icon bubble color {$value->jid|stringToColor}
                        {if="$value->value"}
                            status {$presencestxt[$value->value]}
                        {/if}">
                        <i class="zmdi zmdi-account"></i>
                    </span>
                {/if}

                <span>{$value->getTrueName()}</span>
                <p class="wrap">{$value->status}</p>
            </li>
        {/loop}
        <a class="block large" href="{$c->route('chat')}">
            <li class="action">
                <div class="action">
                    <i class="zmdi zmdi-chevron-right"></i>
                </div>
                <span class="icon">
                    <i class="zmdi zmdi-comments"></i>
                </span>
                <span>{$c->__('hello.chat')}</span>
            </li>
        </a>
    </ul>
    {if="$c->supported('pubsub')"}
        <ul id="news" class="card shadow flex active">
            {if="empty($news)"}
                <li>
                    <span class="icon gray">
                        <i class="zmdi zmdi-info-outline"></i>
                    </span>
                    <span>{$c->__('menu.empty_title')}</span>
                </li>
            {/if}
            {loop="$news"}
                <li class="block condensed"
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
                        <span class="icon thumb color red tiny">
                            +18
                        </span>
                    {elseif="$picture != null"}
                        <span class="icon thumb" style="background-image: url({$picture});"></span>
                    {elseif="$value->node == 'urn:xmpp:microblog:0'"}
                        {$url = $value->getContact()->getPhoto('l')}
                        {if="$url"}
                            <span class="icon thumb" style="background-image: url({$url});">
                            </span>
                        {else}
                            <span class="icon thumb color {$value->getContact()->jid|stringToColor}">
                                <i class="zmdi zmdi-account"></i>
                            </span>
                        {/if}
                    {else}
                        <span class="icon thumb color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
                    {/if}

                    {if="$value->title != null"}
                        <span>{$value->title}</span>
                    {else}
                        <span>{$c->__('hello.contact_post')}</span>
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

                    {if="$value->privacy"}
                        <span class="info" title="{$c->__('menu.public')}">
                            <i class="zmdi zmdi-portable-wifi"></i>
                        </span>
                    {/if}
                    <p>{$value->contentcleaned|strip_tags}</p>

                </li>
            {/loop}
            <a href="{$c->route('news')}">
                <li class="action">
                    <div class="action">
                        <i class="zmdi zmdi-chevron-right"></i>
                    </div>
                    <span class="icon">
                        <i class="zmdi zmdi-receipt"></i>
                    </span>
                    <span>{$c->__('hello.news_page')}</span>
                </li>
            </a>
        </ul>
        <br />
        <ul class="active thick on_desktop">
            <a href="{$c->route('blog', array($jid))}" target="_blank">
                <li class="condensed action">
                    <div class="action">
                        <i class="zmdi zmdi-chevron-right"></i>
                    </div>
                    <span class="icon">
                        <i class="zmdi zmdi-portable-wifi"></i>
                    </span>
                    <span>{$c->__('hello.blog_title')}</span>
                    <p>{$c->__('hello.blog_text')}</p>
                </li>
                <br/>
            </a>
        </ul>
        <ul class="thick flex on_desktop">
            <li class="condensed block">
                <span class="icon bubble color blue">
                    <i class="zmdi zmdi-share"></i>
                </span>
                <span>{$c->__('hello.share_title')}</span>
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
