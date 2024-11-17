{if="$gateways->isNotEmpty()"}
<ul class="list active middle divided">
    <li class="subheader">
        <div>
            <p>{$c->__('account.gateway_title')}</p>
        </div>
    </li>
</ul>

{loop="$gateways"}
<ul class="list active thick">
    <li onclick="Account_ajaxGetRegistration('{$value->server}')">
        <span class="primary icon bubble
            {if="!$value->contact"}color {if="$value->gatewayType"}{$value->gatewayType}{else}gray{/if}{/if}
            status
            {if="$value->presence"} {$value->presence->presencekey} {else}offline disabled{/if}">
            {if="$value->contact"}
                <img src="{$value->contact->getPicture(\Movim\ImageSize::M)}">
            {else}
                <i class="material-symbols">swap_horiz</i>
            {/if}
        </span>
        <span class="control icon gray">
            <i class="material-symbols">chevron_right</i>
        </span>
        <div>
            <p>{$value->name}</p>
            <p>{$value->server}</p>
        </div>
    </li>
</ul>
<div id="gateway_{$value->server|cleanupId}"></div>
<hr />
{/loop}

{/if}
