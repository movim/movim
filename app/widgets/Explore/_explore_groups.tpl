{loop="$groups"}
    <li class="block">
        <a href="{$c->route('server', $value->server)}">
            <span class="tag orange">{$c->__('groups')}</span>
            <span class="tag">{$value->number}</span>
            <span class="content">{$value->server}</span>
        </a>
    </li>
{/loop}
