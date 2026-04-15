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
            {if="$value->getPresence($c->me)"}status {$value->getPresence($c->me)->presencekey} {else}offline disabled{/if}">
            <a href="#" onclick="listIconClick(event);">
                {if="$value->contact"}
                    <img src="{$value->contact->getPicture(\Movim\ImageSize::M)}">
                {else}
                    {if="$value->gatewayType == 'matrix'"}
                        <i class="material-symbols">data_array</i>
                    {elseif="$value->gatewayType == 'telegram'"}
                        <i class="material-symbols">send</i>
                    {elseif="$value->gatewayType == 'discord'"}
                        <i class="material-symbols">sports_esports</i>
                    {else}
                        <i class="material-symbols">swap_horiz</i>
                    {/if}
                {/if}
            </a>
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
