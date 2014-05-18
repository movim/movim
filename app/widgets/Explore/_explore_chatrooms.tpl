{loop="$chatrooms"}
    <li class="block">
        <a href="{$c->route('server', $value->server)}">
            <span class="tag green">{$c->__('chatrooms')}</span>
            {$value->server} 
            <span class="tag">{$value->number}</span>
        </a>
    </li>
{/loop}
