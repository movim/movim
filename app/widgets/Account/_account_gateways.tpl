{if="$gateways->isNotEmpty()"}
<ul class="list active middle divided">
    <li class="subheader">
        <p>{$c->__('account.gateway_title')}</p>
    </li>
    {loop="$gateways"}
    <li onclick="Account_ajaxGetRegistration('{$value->server}')">
        <span class="primary icon bubble color gray
            {if="$value->presence"}status online{/if}">
            <i class="material-icons">swap_horiz</i>
        </span>
        <span class="control icon gray">
            <i class="material-icons">chevron_right</i>
        </span>
        <p>{$value->name}</p>
        <p>{$value->server}</p>
    </li>
    {/loop}
</ul>
{/if}
