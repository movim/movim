{if="$contact->isMe()"}
    <a href="{$c->route('publish')}" class="button action color" title="{$c->__('menu.add_post')}">
        <i class="material-icons">post_add</i>
    </a>
{/if}

<ul class="list thick">
    <li>
        {if="!$contact->isMe()"}
            <span class="control icon active gray" onclick="ContactActions_ajaxChat('{$contact->id|echapJS}')"
                title="{$c->__('button.chat')}">
                <i class="material-icons">comment</i>
            </span>
        {/if}
        {if="$roster"}
            <span class="control icon active gray divided" onclick="ContactHeader_ajaxEditContact('{$contact->id|echapJS}')"
                title="{$c->__('button.edit')}">
                <i class="material-icons">edit</i>
            </span>
            <span class="control icon active gray" onclick="ContactHeader_ajaxDeleteContact('{$contact->id|echapJS}')"
                title="{$c->__('button.delete')}">
                <i class="material-icons">delete</i>
            </span>
        {else}
            {if="$contact->isMe()"}
                <span class="control icon active gray divided" onclick="MovimUtils.redirect('{$c->route('conf')}')"
                    title="{$c->__('button.edit')}">
                    <i class="material-icons">edit</i>
                </span>
            {else}
                <span class="control icon active gray divided" onclick="ContactActions_ajaxAddAsk('{$contact->id|echapJS}')"
                    title="{$c->__('button.add')}">
                    <i class="material-icons">person_add</i>
                </span>
            {/if}
        {/if}
        <span class="primary icon active gray" onclick="history.back()">
            <i class="material-icons">arrow_back</i>
        </span>
        {$url = $contact->getPhoto('m')}
        {if="$url"}
            <span class="primary icon bubble active" onclick="ContactActions_ajaxGetDrawer('{$contact->id|echapJS}')">
                <img src="{$url}">
            </span>
        {/if}
        <div>
            <p class="line active" onclick="ContactActions_ajaxGetDrawer('{$contact->id|echapJS}')">
                {$contact->truename}
                {if="$roster && $roster->group"}
                    <span class="tag color {$roster->group|stringToColor}">{$roster->group}</span>
                {/if}
            </p>
            <p class="line active" onclick="ContactActions_ajaxGetDrawer('{$contact->id|echapJS}')">
                {$contact->id}
            </p>
        </div>
    </li>
</ul>
