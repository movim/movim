<div class="tabelem divided" title="{$c->__('page.feed')}" id="blog" >

    <ul class="thick">
        <li class="action">
            <div class="action">
                <a 
                    href="{$c->route('feed', array($contact->jid))}"
                    target="_blank"
                >
                    <i class="zmdi zmdi-portable-wifi"></i> Atom
                </a>
            </div>
            <span class="icon gray">
                <i class="zmdi zmdi-edit"></i>
            </span>
            <span>
                {if="$contact"}
                <h2>
                    <a href="{$c->route('blog', array($contact->jid))}">
                        {$c->__('blog.title', $contact->getTrueName())}
                    </a>
                </h2>
                {else}
                <h2>
                    <a href="{$c->route('blog', array($contact->jid))}">
                        {$c->__('page.blog')}
                    </a>
                </h2>
                {/if}
            </span>
        </li>
    </ul>

    {loop="$posts"}
        <article>
            <header>
                <ul class="thick">
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
                        <h2>
                            <a href="{$c->route('blog', array($value->origin, $value->nodeid))}">
                                {if="$value->title != null"}
                                    {$value->title}
                                {else}
                                    {$c->__('post.default_title')}
                                {/if}
                            </a>
                        </h2>
                        <p>
                            {if="$value->node == 'urn:xmpp:microblog:0' && $value->getContact()->getTrueName() != ''"}
                                {$value->getContact()->getTrueName()} - 
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
                            <li>
                                <span class="icon small"><img src="http://icons.duckduckgo.com/ip2/{$value.url.host}.ico"/></span>
                                <a href="{$value.href}" class="alternate" target="_blank">
                                    <span>{$value.href|urldecode}</span>
                                </a>
                            </li>
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
            </footer>
            {$comments = $c->getComments($value)}
            {if="$comments"}
                <ul class="spaced middle">
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
                                {$value->getContact()->getTrueName()}
                            </span>
                            <p class="all">
                                {$value->content}
                            </p>
                        </li>
                    {/loop}
                </ul>
            {/if}
        </article>

    {/loop}
    {if="$posts == null"}
        <ul class="simple thick">
            <li>
                <span>{$c->__('blog.empty')}</span>
            </li>
        </ul>
    {/if}
</div>
