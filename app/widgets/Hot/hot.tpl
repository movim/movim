<div class="paddedtop">
    <h2><i class="fa fa-paper-plane-o"></i> {$c->__('explore.hot')}</h2>

    <ul class="list">
    {loop="$nodes"}
        {if="!filter_var($value->server, FILTER_VALIDATE_EMAIL)"}
            <li class="block">
                <a href="{$c->route('node', array($value->server, $value->node))}">
                    <span class="tag gray">{$c->__('post.updated')} {$value->num|strtotime|prepareDate}</span>

                    <span class="content">
                    {if="isset($value->name)"}
                        {$value->name}
                    {else}
                        {$value->node}
                    {/if}
                    - {$value->server}
                    </span>
                    <span class="desc">{$value->description}</span>
                </a>
            </li>
            {/if}
    {/loop}
    </ul>
</div>
