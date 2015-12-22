<div class="card shadow" title="{$c->__('page.feed')}" id="blog" >
    <ul class="list thick">
        {if="$mode == 'blog'"}
        <li>
            <span class="primary icon gray">
                <i class="zmdi zmdi-edit"></i>
            </span>
            <span class="control icon">
                <a
                    href="{$c->route('feed', array($contact->jid))}"
                    target="_blank"
                    title="Atom"
                >
                    <i class="zmdi zmdi-portable-wifi"></i>
                </a>
            </span>
            {if="$contact"}
            <p>
                <a href="{$c->route('blog', array($contact->jid))}">
                    {$c->__('blog.title', $contact->getTrueName())}
                </a>
            </p>
            {else}
            <p>
                <a href="{$c->route('blog', array($contact->jid))}">
                    {$c->__('page.blog')}
                </a>
            </p>
            {/if}
            {if="isset($contact->description)"}
                <p>{$contact->description}</p>
            {/if}
        </li>
        {elseif="$mode == 'tag'"}
        <li>
            <span class="primary icon gray">
                <i class="zmdi zmdi-tag"></i>
            </span>
            <h2>
                <a href="{$c->route('tag', array($tag))}">
                    #{$tag}
                </a>
            </h2>
        </li>
        {else}
            <li>
                <span class="primary icon gray">
                    <i class="zmdi zmdi-pages"></i>
                </span>
                <span class="control icon">
                    <a
                        href="{$c->route('feed', array($server, $node))}"
                        target="_blank"
                    >
                        <i class="zmdi zmdi-portable-wifi"></i>
                    </a>
                </span>
                <p>
                    <a href="{$c->route('node', array($server, $node))}">
                        {if="$item != null"}
                            {if="$item->name"}
                                {$item->name}
                            {else}
                                {$item->node}
                            {/if}
                        {/if}
                    </a>
                </p>
                {if="$item->description"}
                    <p title="{$item->description|strip_tags}">
                        {$item->description|strip_tags}
                    </p>
                {else}
                    <p>{$item->server}</p>
                {/if}
            </li>
        {/if}
    </ul>

    {loop="$posts"}
        {$c->preparePost($value)}
    {/loop}
    {if="isset($more)"}
        <article>
            <ul class="list active">
                {if="$mode == 'blog'"}
                <a href="{$c->route('blog', array($contact->jid, $more))}">
                {else}
                <a href="{$c->route('node', array($server, $node, $more))}">
                {/if}
                    <li id="history" class="block large">
                        <span class="primary icon"><i class="zmdi zmdi-time-restore"></i></span>
                        <p class="normal line">{$c->__('post.older')}</p>
                    </li>
                </a>
            </ul>
        </article>
    {/if}
    {if="$posts == null"}
        <ul class="list simple thick">
            <li>
                <p>{$c->__('blog.empty')}</p>
            </li>
        </ul>
    {/if}

    <ul class="list">
        <li>
            <span class="primary icon gray">
                <i class="zmdi zmdi-cloud-outline"></i>
            </span>
            <p class="center normal"><a target="_blank" href="https://movim.eu">Powered by Movim</a></p>
        </li>
    </ul>
</div>
