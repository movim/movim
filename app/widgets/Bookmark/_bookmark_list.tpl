<ul>
    <li><h2><i class="fa fa-users"></i> {$c->__('title.conferences')}</h2></li>
    {loop="$conferences"}
        <li>
            <a href="#" onclick="{$c->getMucRemove($value)}" class="cross"><i class="fa fa-times oppose"></i></a>
            <a href="#" onclick="{$c->getMucJoin($value)}">{$value->name}</a>
        </li>
    {/loop}

    <li>
    <a class="button color gray oppose" 
       title="{$c->__('chatroom.add')}"
       onclick="movim_toggle_display('#bookmarkmucadd')">
        <i class="fa fa-plus"></i> {$c->__('button.add')}
    </a>
    </li>
</ul>

<div class="clear"></div>

<ul>
    <li><h2><i class="fa fa-bookmark"></i> {$c->__('title.groups')}</h2></li>
    {loop="$subscriptions"}
        {if="$c->checkNewServer($value)"}
            <li>
                <a href="{$c->route('server', $value->server)}">
                    <h3><i class="fa fa-sitemap"></i> {$value->server}</h3>
                </a>
            </li>
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
</ul>
