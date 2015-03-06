<ul class="middle" id="{$jid}_conversation">
    {$messages_html}
    {if="$status != false"}
        <li {if="$myself != false"}class="oppose"{/if}>
            <span class="icon bubble">
                {if="$myself == false"}
                    <img src="{$contact->getPhoto('s')}">
                {else}
                    <img src="{$me->getPhoto('s')}">
                {/if}
            </span>
            <div class="bubble">
                {if="$status == 'composing'"}
                    <i class="md md-mode-edit"></i> {$c->__('message.composing')}
                {else}
                    <i class="md md-mode-edit"></i> {$c->__('message.paused')}
                {/if}
            </div>
        </li>
    {/if}
</ul>
