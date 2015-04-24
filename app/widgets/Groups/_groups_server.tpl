<ul class="middle divided spaced active">
    {loop="$nodes"}
        <li
            class="
                {if="$value->subscription == 'subscribed'"}action{/if}
                condensed
                "
            data-server="{$value->server}"
            data-node="{$value->node}"
            title="{$value->server} - {$value->node}"
        >
            {if="$value->subscription == 'subscribed'"}
                <div class="action">
                    <i class="md md-bookmark"></i>
                </div>
            {/if}
            <span class="icon bubble color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
            <span>
                {if="$value->name"}
                    {$value->name}
                {else}
                    {$value->node}
                {/if}
                
                {if="$value->description"}
                <span class="second">
                    {$value->description|strip_tags}
                </span>
                {/if}                
            </span>
            <p class="wrap">
                {if="$value->sub > 0"}
                    {$c->__('groups.sub', $value->sub)} -
                {/if}
                {$c->__('groups.num', $value->num)}
            </p>
        </li>
    {/loop}
</ul>
<a onclick="Groups_ajaxAdd('{$server}')" class="button action color">
    <i class="md md-add"></i>
</a>
