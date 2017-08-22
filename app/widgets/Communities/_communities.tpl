<ul class="list flex third middle active">
    {loop="$communities"}
        <li
            class="block
                {if="$value->subscription == 'subscribed'"}action{/if}
                {if="$value->occupants > 0 || $value->num > 0"}condensed{/if}
                "
            onclick="MovimUtils.redirect('{$c->route('community', [$value->server, $value->node])}')"
            title="{$value->server} - {$value->node}"
        >
            {if="$value->subscription == 'subscribed'"}
                <span class="control icon gray">
                    <i class="zmdi zmdi-bookmark"></i>
                </span>
            {/if}

            {if="$value->logo"}
                <span class="primary icon bubble">
                    <img src="{$value->getLogo(50)}">
                </span>
            {else}
                <span class="primary icon bubble color {$value->node|stringToColor}">
                    {$value->node|firstLetterCapitalize}
                </span>
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
                {$value->server}
                {if="$value->occupants > 0"}
                    <span title="{$c->__('communitydata.sub', $value->occupants)}">
                        - {$value->occupants} <i class="zmdi zmdi-accounts"></i>
                    </span>
                {/if}
                <span class="info">
                    {$value->published|strtotime|prepareDate:true,true}
                </span>
            </p>
        </li>
    {/loop}
</ul>

