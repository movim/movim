<br />
{if="$item->logo"}
    <li class="block large">
        <p class="center">
            <img src="{$item->getLogo()}" style="max-width: 100%"/>
        </p>
    </li>
    <li class="block large">
        <p>{$item->name}</p>
        <p>
            <!--{$item->created|strtotime|prepareDate:true,true}-->
            {if="$item->num > 0"}
                 {$c->__('communitydata.num', $item->num)}
            {/if}
            {if="$item->sub > 0"}
                <br />{$c->__('communitydata.sub', $item->sub)}
            {/if}
        </p>
    </li>
{/if}
