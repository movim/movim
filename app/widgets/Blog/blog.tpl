<div class="card shadow" title="{$c->__('page.feed')}" id="blog" >
    <ul class="thick">
        {if="$mode == 'blog'"}
        <li class="action {if="isset($contact->description)"}condensed{/if}">
            <div class="action">
                <a
                    href="{$c->route('feed', array($contact->jid))}"
                    target="_blank"
                    title="Atom"
                >
                    <i class="zmdi zmdi-portable-wifi"></i>
                </a>
            </div>
            <span class="icon gray">
                <i class="zmdi zmdi-edit"></i>
            </span>
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
            {if="isset($contact->description)"}
                <p>{$contact->description}</p>
            {/if}
        </li>
        {elseif="$mode == 'tag'"}
        <li class="condensed">
            <span class="icon gray">
                <i class="zmdi zmdi-tag"></i>
            </span>
            <h2>
                <a href="{$c->route('tag', array($tag))}">
                    #{$tag}
                </a>
            </h2>
        </li>
        {else}
        <li class="condensed action">
            <div class="action">
                <a
                    href="{$c->route('feed', array($server, $node))}"
                    target="_blank"
                >
                    <i class="zmdi zmdi-portable-wifi"></i> Atom
                </a>
            </div>
            <span class="icon gray">
                <i class="zmdi zmdi-pages"></i>
            </span>
            <h2>
                <a href="{$c->route('node', array($server, $node))}">
                    {if="$item != null"}
                        {if="$item->name"}
                            {$item->name}
                        {else}
                            {$item->node}
                        {/if}
                    {/if}
                </a>
            </h2>
            {if="$item->description"}
                <h4 title="{$item->description|strip_tags}">
                    {$item->description|strip_tags}
                </h4>
            {else}
                <h4>{$item->server}</h4>
            {/if}
        </li>
        {/if}
    </ul>

    {loop="$posts"}
        {$c->preparePost($value)}
    {/loop}
    {if="isset($more)"}
        <article>
            <ul class="active">
                {if="$mode == 'blog'"}
                <a href="{$c->route('blog', array($contact->jid, $more))}">
                {else}
                <a href="{$c->route('group', array($server, $node, $more))}">
                {/if}
                    <li id="history" class="block large">
                        <span class="icon"><i class="zmdi zmdi-time-restore"></i></span>
                        <span>{$c->__('post.older')}</span>
                    </li>
                </a>
            </ul>
        </article>
    {/if}
    {if="$posts == null"}
        <ul class="simple thick">
            <li>
                <span>{$c->__('blog.empty')}</span>
            </li>
        </ul>
    {/if}

    <ul>
        <li>
            <a target="_blank" href="https://movim.eu">
                <span class="icon">
                    <i class="zmdi zmdi-cloud-outline"></i>
                </span>
                <span>Powered by Movim</span>
            </a>
        </li>
    </ul>
</div>
