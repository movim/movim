{if="count($subscriptions) > 0"}
    <ul class="list active flex">
        <li class="subheader block large">
            <p>
                <span class="info">{$subscriptions|count}</span>
                {$c->__('group.subscriptions')}
            </p>
        </li>
        {loop="$subscriptions"}
            <li class="block"
                title="{$value->server} - {$value->node}"
                onclick="MovimUtils.redirect('{$c->route('community', [$value->server, $value->node])}')">
                {if="$value->logo"}
                    <span class="primary icon bubble">
                        <img src="{$value->getLogo(50)}">
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
                {/if}
                <span class="control icon gray">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>
                <p class="line normal">
                    {if="$value->name"}
                        {$value->name}
                    {else}
                        {$value->node}
                    {/if}
                </p>
                {if="$value->description"}
                    <p class="line">{$value->description|strip_tags}</p>
                {/if}
            </li>
        {/loop}
    </ul>
{/if}

<br />
