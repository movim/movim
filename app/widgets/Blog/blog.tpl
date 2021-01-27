<header>
    {if="$mode == 'blog'"}
    <ul class="list thick">
        <li>
            <span class="primary icon gray">
                <i class="material-icons">edit</i>
            </span>
            {if="$contact"}
                <span class="control icon active">
                    <a
                        href="{$c->route('feed', $contact->jid)}"
                        target="_blank"
                        title="Atom"
                    >
                        <i class="material-icons">rss_feed</i>
                    </a>
                </span>
                <div>
                    <p>
                        <a href="{$contact->getBlogUrl()}">
                            {$c->__('blog.title', $contact->truename)}
                        </a>
                    </p>
                        {if="$contact->description"}
                            <p>{$contact->description}</p>
                        {/if}
                    <p>
                        {$c->__('page.blog')}
                    </p>
                </div>
            {/if}
        </li>
    </ul>
    {elseif="$mode == 'tag'"}
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
    {elseif="$node && $server"}
        <ul class="list thick">
            <li>
                {$url = null}
                {if="$item != null"}
                    {$url = $item->getPhoto('m')}
                {/if}
                {if="$url"}
                    <span class="primary icon bubble">
                        <img src="{$url}"/>
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
                            <p title="{$item->description|stripTags}">
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
    {else}
        <ul class="list thick">
            <li>
                <div>
                    <p>{$c->__('post.empty')}</p>
                </div>
            </li>
        </ul>
    {/if}
</header>

<div class="card shadow" title="{$c->__('page.feed')}" id="blog" >
    {if="$posts == null || $posts->isEmpty()"}
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
            <div id="{$value->nodeid|cleanupId}" class="block large">
                {autoescape="off"}
                    {$c->preparePost($value)}
                {/autoescape}
            </div>
        {/loop}
    {/if}
    {if="isset($next)"}
        <article>
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
        </article>
    {/if}
</div>
