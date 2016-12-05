<br />
{if="$item->logo"}
    <li class="block large">
        <p class="center">
            <img src="{$item->getLogo(400)}" style="max-width: 100%"/>
        </p>
    </li>
    <li class="block large">
        <p>{$item->name}</p>
        <p>{$item->created|strtotime|prepareDate:true,true}</p>
    </li>
{/if}
