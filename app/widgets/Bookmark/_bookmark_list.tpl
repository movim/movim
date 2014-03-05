<h2>{$c->t('Conferences')}</h2>
{loop="conferences"}
    <li>
        <a href="#" onclick="{$c->getMucJoin($value)}">{$value->name}</a>
        <a href="#" onclick="{$c->getMucRemove($value)}">X</a>
    </li>
{/loop}

<h2>{$c->t('Groups')}</h2>
{loop="subscriptions"}
    {if="$c->checkNewServer($value)"}
        <a href="{$c->route('server', $value->server)}">
            <h3>{$value->server}</h3>
        </a>
    {/if}
    <li>
        
        <a href="{$c->route('node', array($value->server, $value->node))}">{$value->name}</a>
    </li>
{/loop}
