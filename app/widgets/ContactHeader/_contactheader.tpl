<ul class="list middle">
    <li>
        {if="$roster"}
            <span class="control icon active gray" onclick="ContactHeader_ajaxEditContact('{$contact->id|echapJS}')"
                title="{$c->__('button.edit')}">
                <i class="material-icons">edit</i>
            </span>
            <span class="control icon active gray" onclick="ContactHeader_ajaxDeleteContact('{$contact->id|echapJS}')"
                title="{$c->__('button.delete')}">
                <i class="material-icons">delete</i>
            </span>
        {else}
            {if="$contact->isMe()"}
                <span class="control icon active gray" onclick="MovimUtils.redirect('{$c->route('conf')}')"
                    title="{$c->__('button.add')}">
                    <i class="material-icons">settings</i>
                </span>
            {else}
                <span class="control icon active gray" onclick="ContactActions_ajaxAddAsk('{$contact->id|echapJS}')"
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
            <span class="primary icon bubble">
                <img src="{$url}">
            </span>
        {/if}
        <span class="control active icon gray" onclick="ContactActions_ajaxGetDrawer('{$contact->id|echapJS}')">
            <i class="material-icons">more_horiz</i>
        </span>
        <content>
            <p class="line">
                {$contact->truename}
                {if="$roster && $roster->group"}
                    <span class="tag color {$roster->group|stringToColor}">{$roster->group}</span>
                {/if}
            </p>
            <p class="line">
                {$contact->id}
            </p>
        </content>
    </li>
</ul>
