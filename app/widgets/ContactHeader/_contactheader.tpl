<ul class="list thick">
    <li>
        {if="$contactr != null"}
            <span class="control icon active gray" onclick="ContactHeader_ajaxEditContact('{$contact->jid|echapJS}')">
                <i class="zmdi zmdi-edit"></i>
            </span>
            <span class="control icon active gray" onclick="ContactHeader_ajaxDeleteContact('{$contact->jid|echapJS}')">
                <i class="zmdi zmdi-delete"></i>
            </span>
        {else}
            {if="!$contact->isMe()"}
            <!--<span class="control icon active gray" onclick="Roster_ajaxDisplaySearch('{$contact->jid}')">
                <i class="zmdi zmdi-account-add"></i>
            </span>-->
            {/if}
        {/if}
        <span class="primary icon active gray" onclick="history.back()">
            <i class="zmdi zmdi-arrow-back"></i>
        </span>
        <p class="line">
            {$contact->getTrueName()}
        </p>
        <p class="line">{$contact->jid}</p>
    </li>
</ul>
{if="$contact->isMe()"}
<button class="button action color"
    title="{$c->__('publishbrief.post')}"
    onclick="MovimUtils.reload('{$c->route('publish')}')">
    <i class="zmdi zmdi-edit"></i>
</button>
{else}
<button onclick="ContactHeader_ajaxChat('{$contact->jid|echapJS}')" class="button action color on_mobile">
    <i class="zmdi zmdi-comment-text-alt"></i>
</button>
{/if}
