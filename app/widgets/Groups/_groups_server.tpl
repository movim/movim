<ul class="list middle divided spaced active all">
    <li class="subheader" onclick="Groups_ajaxSubscriptions()">
        <span class="primary icon"><i class="zmdi zmdi-arrow-back"></i></span>
        <p class="normal"><span class="info">{$nodes|count}</span>{$server}</p>
    </li>
    {loop="$nodes"}
        <li
            class="
                {if="$value->subscription == 'subscribed'"}action{/if}
                {if="$value->sub > 0 || $value->num > 0"}condensed{/if}
                "
            data-server="{$value->server}"
            data-node="{$value->node}"
            title="{$value->server} - {$value->node}"
        >
            {if="$value->subscription == 'subscribed'"}
                <span class="control icon gray">
                    <i class="zmdi zmdi-bookmark"></i>
                </span>
            {/if}

            {if="$value->logo"}
                <span class="primary icon bubble">
                    <img src="{$value->getLogo()}">
                </span>
            {else}
                <span class="primary icon bubble color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
            {/if}
            <p class="line">
                {if="$value->name"}
                    {$value->name}
                {else}
                    {$value->node}
                {/if}
                <span class="second">
                    {if="$value->description"}
                        {$value->description|strip_tags}
                    {/if}
                </span>
            </p>
            <p>
                {if="isset($value->sub)"}
                    {if="$value->sub > 0"}
                        {$c->__('groups.sub', $value->sub)}
                    {/if}
                    {if="$value->sub > 0 && $value->num > 0"}
                      -
                    {/if}
                    {if="$value->num > 0"}
                         {$c->__('groups.num', $value->num)}
                    {/if}
                {else}
                    {$value->node}
                {/if}
            </p>
        </li>
    {/loop}
</ul>
<a onclick="Groups_ajaxTestAdd('{$server}')" class="button action color">
    <i class="zmdi zmdi-plus"></i>
</a>
