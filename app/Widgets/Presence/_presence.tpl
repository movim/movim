<li>
    <span
        onclick="MovimUtils.reload('{$c->route('contact', $me->id)}')"
        class="primary icon bubble status
        {if="$presence->value != null"}{$presencetxt[$presence->value]}{/if}
    ">
        <img src="{$me->getPicture(\Movim\ImageSize::M)}">
    </span>
    <span class="control icon active toggleable"
        onclick="Presence_ajaxAskLogout()"
        title="{$c->__('status.disconnect')}">
        <i class="material-symbols">exit_to_app</i>
    </span>
    <div>
        <p class="line bold" onclick="MovimUtils.reload('{$c->route('contact', $me->id)}')">
            {$me->truename}
        </p>
    </div>
</li>
