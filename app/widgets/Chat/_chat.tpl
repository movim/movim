<ul class="thick">
    {loop="$messages"}
        <li {if="$value->jidfrom == $jid"}class="oppose"{/if}>
            <span class="icon bubble">
                {if="$value->jidfrom == $jid"}
                    <img src="{$contact->getPhoto('s')}">
                {else}
                    <img src="{$me->getPhoto('s')}">
                {/if}
            </span>
            <div class="bubble">
                {$value->body|prepareString}
            <span class="info">{$value->delivered|strtotime|prepareDate}</span>
            </div>
        </li>
    {/loop}
</ul>
