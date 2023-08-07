<div id="blog">
    {if="$mode == 'blog'"}
        {if="$contact && $contact->isPublic()"}
            {$banner = $contact->getBanner()}
            <header class="big top color {if="$contact"}{$contact->jid|stringToColor}{/if}"
                style="
                        background-image:
                        linear-gradient(to top, rgba(23,23,23,0.9) 0, rgba(23,23,23,0.6) 5rem, rgba(23,23,23,0) 12rem)
                        {if="$banner"}
                            , url('{$banner}')
                        {/if}
                        ;
                    ">
                <ul class="list thick">
                    <li>
                        <span class="primary icon on_desktop">
                            <i class="material-icons">person</i>
                        </span>
                        <span class="primary icon bubble on_mobile">
                            <img src="{$contact->getPicture('m')}">
                        </span>
                        <span class="control icon active" onclick="MovimUtils.openInNew('{$c->route('feed', $contact->jid)}')">
                            <i class="material-icons">rss_feed</i>
                        </span>
                        <div>
                            <p>
                                {$c->__('blog.title', $contact->truename)}
                            </p>
                            <p>
                                {$c->__('page.blog')}
                            </p>
                        </div>
                    </li>
                </ul>
            </header>
        {/if}
    {elseif="$mode == 'tag'"}
        <header>
            <ul class="list middle">
                <li>
                    {if="isLogged()"}
                    <span class="primary icon active gray" onclick="history.back()">
                        <i class="material-icons">arrow_back</i>
                    </span>
                    {else}
                    <span class="primary icon gray">
                        <i class="material-icons">label</i>
                    </span>
                    {/if}
                    <div>
                        <p>
                            <a href="{$c->route('tag', array($tag))}">
                                <i class="material-icons">tag</i>{$tag}
                            </a>
                        </p>
                    </div>
                </li>
            </ul>
        </header>
    {elseif="$node && $server"}
        <header>
            <ul class="list thick">
                <li>
                    {if="$item"}
                        <span class="primary icon bubble">
                            <img src="{$item->getPicture('m')}"/>
                        </span>
                    {else}
                        <span class="primary icon gray">
                            <i class="material-icons">group_work</i>
                        </span>
                    {/if}
                    <span class="control icon active">
                        <a
                            href="{$c->route('feed', [$server, $node])}"
                            target="_blank"
                            title="Atom"
                        >
                            <i class="material-icons">rss_feed</i>
                        </a>
                    </span>
                    <div>
                        <a class="button oppose color gray" title="{$c->__('communityheader.subscribe')}"
                            href="xmpp:{$server}?pubsub;action=subscribe;node={$node}">
                            <i class="material-icons">add</i> <span class="on_desktop">{$c->__('communityheader.subscribe')}</span>
                        </a>
                        <p>
                            <a href="{$c->route('node', [$server, $node])}">
                                {if="$item != null && $item->name"}
                                    {$item->name}
                                {else}
                                    {$node}
                                {/if}
                            </a>
                        </p>
                        {if="$item != null"}
                            {if="$item->description"}
                                <p title="{$item->description|stripTags}" class="line">
                                    <i class="material-icons">people</i> {$c->__('communitydata.sub', $item->occupants)} ·
                                    {$item->description|stripTags}
                                </p>
                            {else}
                                <p>
                                    <i class="material-icons">people</i> {$c->__('communitydata.sub', $item->occupants)} ·
                                    {$item->server}
                                </p>
                            {/if}
                        {/if}
                    </div>
                </li>
            </ul>
        </header>
    {else}
        <header>
            <ul class="list thick">
                <li>
                    <div>
                        <p>{$c->__('post.empty')}</p>
                    </div>
                </li>
            </ul>
        </header>
    {/if}
</header>

<ul class="list card shadow {if="$gallery"}middle flex third gallery large active{/if}">
    {if="$posts == null || $posts->isEmpty()"}
        <br />
        <article class="block">
            <ul class="list simple thick">
                <li>
                    <span class="primary icon gray">
                        <i class="material-icons">comment</i>
                    </span>
                    <div>
                        <p class="normal">{$c->__('post.empty')}</p>
                    </div>
                </li>
            </ul>
        </article>
    {else}
        {loop="$posts"}
            {if="$gallery"}
                {autoescape="off"}
                    {$c->prepareTicket($value)}
                {/autoescape}
            {else}
                <div id="{$value->nodeid|cleanupId}" class="block">
                    {autoescape="off"}
                        {$c->preparePost($value)}
                    {/autoescape}
                </div>
            {/if}
        {/loop}
    {/if}
</ul>

{if="isset($next)"}
    <ul class="list active thick">
        <a href="{$next}">
            <li id="history" class="block large">
                <span class="primary icon gray"><i class="material-icons">history</i></span>
                <div>
                    <p class="normal line center">{$c->__('post.older')}</p>
                </div>
            </li>
        </a>
    </ul>
{/if}

</div>
