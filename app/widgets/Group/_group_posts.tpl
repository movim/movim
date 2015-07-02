{loop="$posts"}
    <article id="{$value->nodeid}">
        <header>
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
                        {if="$value->title != null"}
                            {$value->title}
                        {else}
                            {$c->__('post.default_title')}
                        {/if}
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
            {$value->contentcleaned}
        </section>
        <footer>
            <ul class="thin">
                {if="isset($value->getAttachements().links)"}
                    {loop="$value->getAttachements().links"}
                        {if="substr($value.href, 0, 5) != 'xmpp:'"}
                        <li>
                            <span class="icon small"><img src="http://icons.duckduckgo.com/ip2/{$value.url.host}.ico"/></span>
                            <a href="{$value.href}" class="alternate" target="_blank">
                                <span>{$value.href|urldecode}</span>
                            </a>
                        </li>
                        {/if}
                    {/loop}
                {/if}
                {if="isset($value->getAttachements().files)"}
                    {loop="$value->getAttachements().files"}
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
            {if="isset($value->getAttachements().pictures)"}
                <ul class="flex middle">
                {loop="$value->getAttachements().pictures"}
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
    </article>
{/loop}
{if="$posts != null"}
<ul class="active thick">
    <li onclick="Group_ajaxGetHistory('{$server}', '{$node}', {$page+1}); this.parentNode.parentNode.removeChild(this.parentNode);">
        <span class="icon">
            <i class="zmdi zmdi-time-restore"></i>
        </span>
        {$c->__('post.older')}
    </li>
</ul>
{/if}
