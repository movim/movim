<div class="tabelem divided" title="{$c->__('page.feed')}" id="blog" >

    <ul class="thick">
        <li class="action">
            <div class="action">
                <a 
                    href="{$c->route('feed', array($contact->jid, 'urn:xmpp:microblog:0'))}"
                    target="_blank"
                >
                    <i class="md md-wifi-tethering"></i> Atom
                </a>
            </div>
            <span class="icon gray">
                <i class="md md-edit"></i>
            </span>
            <span>
                <h2>{$c->__('blog.title', $contact->getTrueName())}</h2>
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
                                <i class="md md-person"></i>
                            </span>
                        {/if}
                        <h2>
                            {if="$value->title != null"}
                                {$value->title}
                            {else}
                                {$c->__('post.default_title')}
                            {/if}
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
                                        <span class="md md-attach-file"></span>
                                    </span>
                                    <span>{$value.href|urldecode}</span>
                                </a>
                            </li>
                        {/loop}
                    {/if}
                </ul>
            </footer>
        </article>
    {/loop}
</div>
