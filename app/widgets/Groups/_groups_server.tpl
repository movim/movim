<ul class="middle divided spaced active">
    {loop="$nodes"}
        <li
            class="
                {if="$value->subscription == 'subscribed'"}action{/if}
                {if="$value->description"}condensed{/if}
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
                <span class="second">{$value->num}</span>
            </span>
            {if="$value->description"}
                <p class="wrap">{$value->description|strip_tags}</p>
            {/if}
        </li>
    {/loop}
</ul>
