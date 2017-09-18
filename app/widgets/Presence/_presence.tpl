<a href="{$c->route('contact', $me->jid)}">
    <li title="{$c->__('privacy.my_profile')}">
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

        <p class="line bold normal">{$me->getTrueName()}</p>
    </li>
</a>
