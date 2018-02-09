<li title="{$c->__('privacy.my_profile')}">
    {$url = $me->getPhoto('s')}
    {if="$url"}
        <span
            onclick="MovimUtils.reload('{$c->route('contact', $me->jid)}')"
            class="primary icon bubble status {if="$presence->value != null"}{$presencetxt[$presence->value]}{/if}"
            style="background-image: url({$me->getPhoto('s')})">
        </span>
    {else}
        <span
            onclick="MovimUtils.reload('{$c->route('contact', $me->jid)}')"
            class="primary icon bubble color {$me->jid|stringToColor} status {if="$presence->value != null"}{$presencetxt[$presence->value]}{/if}">
            <i class="zmdi zmdi-account"></i>
        </span>
    {/if}
    <span class="control icon active on_mobile"
        onclick="Presence_ajaxLogout()"
        title="{$c->__('status.disconnect')}">
        <i class="zmdi zmdi-sign-in"></i>
    </span>
    <p class="line bold normal" onclick="MovimUtils.reload('{$c->route('contact', $me->jid)}')">
        {$me->getTrueName()}
    </p>
</li>
