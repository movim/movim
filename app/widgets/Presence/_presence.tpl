<li>
    {$url = $me->getPhoto()}
    {if="$url"}
        <span
            onclick="MovimUtils.reload('{$c->route('contact', $me->jid)}')"
            class="primary icon bubble status
            {if="$presence->value != null"}{$presencetxt[$presence->value]}{/if}
            {if="$me->hasLocation()"} location{/if}
        "
            style="background-image: url({$url})">
        </span>
    {else}
        <span
            onclick="MovimUtils.reload('{$c->route('contact', $me->jid)}')"
            class="primary icon bubble color {$me->jid|stringToColor} status
                {if="$presence->value != null"}{$presencetxt[$presence->value]}{/if}
                {if="$me->hasLocation()"} location{/if}
            ">
            <i class="material-icons">person</i>
        </span>
    {/if}
    <div>
        <p class="line bold normal" onclick="MovimUtils.reload('{$c->route('contact', $me->jid)}')">
            {$me->truename}
        </p>
    </div>
</li>
