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
                        <span class="icon bubble">
                            <img src="{$value->getContact()->getPhoto('s')}">
                        </span>
                        <h2>
                            {if="$value->title != null"}
                                {$value->title}
                            {else}
                                {$c->__('post.default_title')}
                            {/if}
                        </h2>
                        <p>
                            {if="$value->node == 'urn:xmpp:microblog:0'"}
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
        </article>
    {/loop}
</div>
