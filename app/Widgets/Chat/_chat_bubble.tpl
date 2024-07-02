<li {if="$me"}class="oppose"{/if}>
    {if="$muc"}
        <span class="primary icon bubble" onclick="Chat.quoteMUC(this.dataset.resource, true);"></span>
        <span class="control icon bubble"></span>
    {else}
        <span class="primary icon bubble">
            {if="$me == null"}
                <a href="#" onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')">
            {/if}
                <img src="{$contact->getPicture()}" data-name="{$contact->truename}">
            {if="$me == null"}</a>{/if}
        </span>
    {/if}

    <div class="bubble">
        <div class="message">
            <p></p>
            <ul class="reactions"></ul>
            <span class="info"></span>
            <span class="reaction" title="{$c->__('message.react')}">
                <i class="material-symbols">add_reaction</i>
            </span>
            <span class="reply">
                <i class="material-symbols">reply</i>
            </span>
        </div>
    </div>
</li>
