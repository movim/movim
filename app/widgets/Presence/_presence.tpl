<li title="{$c->__('privacy.my_profile')}">
    {$url = $me->getPhoto()}
    {if="$url"}
        <span
            onclick="MovimUtils.reload('{$c->route('contact', $me->jid)}')"
            class="primary icon bubble status {if="$presence->value != null"}{$presencetxt[$presence->value]}{/if}"
            style="background-image: url({$url})">
        </span>
    {else}
        <span
            onclick="MovimUtils.reload('{$c->route('contact', $me->jid)}')"
            class="primary icon bubble color {$me->jid|stringToColor} status {if="$presence->value != null"}{$presencetxt[$presence->value]}{/if}">
            <i class="material-icons">person</i>
        </span>
    {/if}
    <span class="control icon active on_mobile"
        onclick="Presence_ajaxLogout()"
        title="{$c->__('status.disconnect')}">
        <i class="material-icons">exit_to_app</i>
    </span>
    <p class="line bold normal" onclick="MovimUtils.reload('{$c->route('contact', $me->jid)}')">
        {$me->truename}
    </p>
</li>
