<li>
    <span
        onclick="MovimUtils.reload('{$c->route('contact', $me->jid)}')"
        class="primary icon bubble status
        {if="$presence->value != null"}{$presencetxt[$presence->value]}{/if}
        {if="$me->hasLocation()"} location{/if}
    ">
        <img src="{$me->getPicture('m')}">
    </span>
    <span class="control icon active"
        onclick="Presence_ajaxAskLogout()"
        title="{$c->__('status.disconnect')}">
        <i class="material-symbols">exit_to_app</i>
    </span>
    <div>
        <p class="line bold normal" onclick="MovimUtils.reload('{$c->route('contact', $me->jid)}')">
            {$me->truename}
        </p>
    </div>
</li>
