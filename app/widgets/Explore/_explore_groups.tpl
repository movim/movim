{loop="$groups"}
    <li class="block">
        <a href="{$c->route('server', $value->server)}">
            <span class="tag orange">{$c->__('groups')}</span>
            {$value->server} 
            <span class="tag">{$value->number}</span>
        </a>
    </li>
{/loop}
