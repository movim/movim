<li>
    <span class="primary icon bubble color transparent">
        {if="$list == null"}
            <img src="{$contact->getPicture()}">
        {else}
            <i class="material-symbols tiny">edit</i>
        {/if}
    </span>
    <div class="bubble">
        <div class="message" title="{$c->__('message.composing')}">
            <span class="dots"></span>
            <p>{if="$list != null"}{$list}{/if}</p>
        </div>
    </div>
</li>
