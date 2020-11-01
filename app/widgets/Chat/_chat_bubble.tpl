<li {if="$me"}class="oppose"{/if}>
    {if="$muc"}
        <span class="primary icon bubble" onclick="Chat.quoteMUC(this.dataset.resource, true);"></span>
        <span class="control icon bubble"></span>
    {else}
        {$url = $contact->getPhoto()}
        {if="$url"}
            <span class="primary icon bubble">
                {if="$me == null"}
                    <a href="#" onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')">
                {/if}
                    <img src="{$url}">
                {if="$me == null"}</a>{/if}
            </span>
        {else}
            <span class="primary icon bubble color {$contact->jid|stringToColor}">
                {if="$me == null"}
                    <a href="#" onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')">
                {/if}
                    <i class="material-icons">person</i>
                {if="$me == null"}</a>{/if}
            </span>
        {/if}
    {/if}

    <div class="bubble">
        <div>
            <p></p>
            <ul class="reactions"></ul>
            <span class="info"></span>
            <span class="reaction" title="{$c->__('message.react')}">
                +<i class="material-icons">mood</i>
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
