<li {if="$me"}class="oppose"{/if}>
    {if="$muc"}
        <span class="primary icon bubble" onclick="Chat.quoteMUC(this.dataset.resource, true);"></span>
        <span class="control icon bubble"></span>
    {else}
        <span class="primary icon bubble">
            {if="$me == null"}
                <a href="#" onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')">
            {/if}
                <img src="{$contact->getPhoto()}">
            {if="$me == null"}</a>{/if}
        </span>
    {/if}

    <div class="bubble">
        <div>
            <p></p>
            <ul class="reactions"></ul>
            <span class="info"></span>
            <span class="reaction" title="{$c->__('message.react')}">
                <i class="material-icons">add_reaction</i>
            </span>
            <span class="reply" title="{$c->__('button.reply')}">
                <i class="material-icons">reply</i>
            </span>
            {if="!$muc"}
                <span class="actions">
                    <i class="material-icons">more_vert</i>
                </span>
            {/if}
        </div>
    </div>
</li>
