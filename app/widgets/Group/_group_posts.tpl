{loop="$posts"}
    {$attachements = $value->getAttachements()}
    <article id="{$value->nodeid}" class="block">
        {if="isset($attachements.pictures)"}
        <header
            class="big"
            style="
                background-image: linear-gradient(to bottom, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.5) 100%), url('{$attachements['pictures'][0]['href']}');">
        {else}
        <header>
        {/if}
            <ul class="thick">
                <li class="condensed">
                    {$url = $value->getContact()->getPhoto('s')}
                    {if="$url"}
                        <span class="icon bubble">
                            <img src="{$url}">
                        </span>
                    {else}
                        <span class="icon bubble color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
                    {/if}
                    <h2>
                        <a href="{$c->route('news', $value->nodeid)}">
                            {if="$value->title != null"}
                                {$value->title}
                            {else}
                                {$c->__('post.default_title')}
                            {/if}
                        </a>
                    </h2>
                    <p>
                        {if="$value->getContact()->getTrueName() != ''"}
                            <a href="{$c->route('contact', $value->getContact()->jid)}">
                                {$value->getContact()->getTrueName()}
                            </a>
                             -
                        {/if}
                        {$value->published|strtotime|prepareDate}
                    </p>
                </li>
            </ul>
        </header>
        <section>
            <content>
                {$value->contentcleaned}
            </content>
        </section>
        <footer>
            <ul class="thin">
                {if="isset($attachements.links)"}
                    {loop="$attachements.links"}
                        {if="substr($value.href, 0, 5) != 'xmpp:' && filter_var($value.href, FILTER_VALIDATE_URL)"}
                        <li>
                            <span class="icon small"><img src="http://icons.duckduckgo.com/ip2/{$value.url.host}.ico"/></span>
                            <a href="{$value.href}" class="alternate" target="_blank">
                                <span>{$value.href|urldecode}</span>
                            </a>
                        </li>
                        {/if}
                    {/loop}
                {/if}
                {if="isset($attachements.files)"}
                    {loop="$attachements.files"}
                        <li>
                            <a
                                href="{$value.href}"
                                class="enclosure"
                                type="{$value.type}"
                                target="_blank">
                                <span class="icon small gray">
                                    <span class="zmdi zmdi-attachment-alt"></span>
                                </span>
                                <span>{$value.href|urldecode}</span>
                            </a>
                        </li>
                    {/loop}
                {/if}
            </ul>
            {if="isset($attachements.pictures)"}
                <ul class="flex middle">
                {loop="$attachements.pictures"}
                    <li class="block pic">
                        <span class="icon small gray">
                            <i class="zmdi zmdi-image"></i>
                        </span>
                        <a href="{$value.href}" class="alternate" target="_blank">
                            <img type="{$value.type}" src="{$value.href|urldecode}"/>
                        </a>
                    </li>
                {/loop}
                </ul>
            {/if}
        </footer>
        {$comments = $c->getComments($value)}
        {if="$comments"}
            <ul class="divided spaced middle">
                <li class="subheader">
                    {$c->__('post.comments')}
                    <span class="info">{$comments|count}</span>
                </li>
                {loop="$comments"}
                    <li class="condensed">
                        {$url = $value->getContact()->getPhoto('s')}
                        {if="$url"}
                            <span class="icon bubble">
                                <img src="{$url}">
                            </span>
                        {else}
                            <span class="icon bubble color {$value->getContact()->jid|stringToColor}">
                                <i class="zmdi zmdi-account"></i>
                            </span>
                        {/if}
                        <span class="info">{$value->published|strtotime|prepareDate}</span>
                        <span>
                            <a href="{$c->route('contact', $value->getContact()->jid)}">
                                {$value->getContact()->getTrueName()}
                            </a>
                        </span>
                        <p class="all">
                            {$value->content}
                        </p>
                    </li>
                {/loop}
                <a href="{$c->route('news', $value->nodeid)}">
                    <li class="action">
                        <div class="action">
                            <i class="zmdi zmdi-chevron-right"></i>
                        </div>
                        <span class="icon">
                            <i class="zmdi zmdi-comment"></i>
                        </span>
                        <span>{$c->__('post.comment_add')}</span>
                    </li>
                </a>
            </ul>
        {/if}
        <br />
    </article>
{/loop}
{if="$posts != null && count($posts) >= $paging-1"}
<ul class="active thick">
    <li onclick="Group_ajaxGetHistory('{$server}', '{$node}', {$page+1}); this.parentNode.parentNode.removeChild(this.parentNode);">
        <span class="icon">
            <i class="zmdi zmdi-time-restore"></i>
        </span>
        {$c->__('post.older')}
    </li>
</ul>
{/if}
