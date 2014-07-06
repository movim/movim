<div class="paddedtop">
    <h2>{$c->__('explore.hot')}</h2>

    <ul class="list">
    {loop="$nodes"}
        <li class="block">
            <a href="{$c->route('node', array($value->server, $value->node))}">
                <span class="tag gray">{$c->__('post.updated')} {$value->num|strtotime|prepareDate}</span>
                <span class="tag desc">{$value->description}</span>
                <span class="content">
                {if="isset($value->name)"}
                    {$value->name}
                {else}
                    {$value->node}
                {/if}
                - {$value->server}
                </span>
            </a>
        </li>
    {/loop}
    </ul>
</div>
