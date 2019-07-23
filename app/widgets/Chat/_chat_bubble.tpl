<li {if="$me"}class="oppose"{/if}>
    {if="$muc"}
        <span class="primary icon bubble" onclick="Chat.quoteMUC(this.dataset.resource, true);"></span>
        <span class="control icon bubble"></span>
    {else}
        {$url = $contact->getPhoto()}
        {if="$url"}
            <span class="{if="$me"}control{else}primary{/if} icon bubble">
                {if="$me == null"}
                    <a href="#" onclick="Chat_ajaxGetContact('{$contact->jid|echapJS}')">
                {/if}
                    <img src="{$url}">
                {if="$me == null"}</a>{/if}
            </span>
        {else}
            <span class="{if="$me"}control{else}primary{/if} icon bubble color {$contact->jid|stringToColor}">
                {if="$me == null"}
                    <a href="#" onclick="Chat_ajaxGetContact('{$contact->jid|echapJS}')">
                {/if}
                    <i class="material-icons">person</i>
                {if="$me == null"}</a>{/if}
            </span>
        {/if}
    {/if}

    <div class="bubble">
        <div>
            <span class="info"></span>
            <p></p>
            <ul class="reactions"></ul>
            <span class="reaction">
                +<i class="material-icons">mood</i>
            </span>
        </div>
    </div>
</li>
