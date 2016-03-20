<li onclick="{$dialog} MovimTpl.hideMenu()">
    {$url = $me->getPhoto('s')}
    {if="$url"}
        <span
            class="primary icon bubble status {if="$presence->value != null"}{$presencetxt[$presence->value]}{/if}"
            style="background-image: url({$me->getPhoto('s')})">
        </span>
    {else}
        <span class="primary icon bubble color {$me->jid|stringToColor} status {if="$presence->value != null"}{$presencetxt[$presence->value]}{/if}">
            <i class="zmdi zmdi-account"></i>
        </span>
    {/if}

    <span class="control icon">
        <i class="zmdi zmdi-caret-down"></i>
    </span>
    <p class="line bold">{$me->getTrueName()}</p>
    <p class="line">{$presence->status}</p>
</li>
