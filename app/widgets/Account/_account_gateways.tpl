{if="$gateways->isNotEmpty()"}
<ul class="list fill active middle divided">
    <li class="subheader">
        <div>
            <p>{$c->__('account.gateway_title')}</p>
        </div>
    </li>
    {loop="$gateways"}
    <li onclick="Account_ajaxGetRegistration('{$value->server}')">
        <span class="primary icon bubble color gray status
            {if="$value->presence"}{getPresencesTxt($value->presence->value)}{else}offline{/if}">
            <i class="material-icons">swap_horiz</i>
        </span>
        <span class="control icon gray">
            <i class="material-icons">chevron_right</i>
        </span>
        <div>
            <p>{$value->name}</p>
            <p>{$value->server}</p>
        </div>
    </li>
    {/loop}
</ul>
{/if}
