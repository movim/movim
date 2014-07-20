<h2><i class="fa fa-users"></i> {$c->__('title.conferences')}</h2>
<ul>
    {loop="$conferences"}
        <li>
            <a href="#" onclick="{$c->getMucJoin($value)}">{$value->name}</a>
            <a href="#" onclick="{$c->getMucRemove($value)}">X</a>
        </li>
    {/loop}
</ul>

<h2><i class="fa fa-bookmark-o"></i> {$c->__('title.groups')}</h2>
{loop="$subscriptions"}
    {if="$c->checkNewServer($value)"}
        <a href="{$c->route('server', $value->server)}">
            <h3><i class="fa fa-sitemap"></i> {$value->server}</h3>
        </a>
    {/if}
    <li>
        
        <a href="{$c->route('node', array($value->server, $value->node))}">
        {if="$value->name"}
            {$value->name}
        {else}
            {$value->node}
        {/if}
        </a>
    </li>
{/loop}
