{if="$contact->isMe()"}
    <a href="{$c->route('publish')}" class="button action color" title="{$c->__('menu.add_post')}">
        <i class="material-icons">post_add</i>
    </a>
{/if}

<header class="big top color {$contact->jid|stringToColor}"
        style="
                background-image:
                linear-gradient(to top, rgba(23,23,23,0.9) 0, rgba(23,23,23,0.6) 5rem, rgba(23,23,23,0) 12rem), url('{$contact->getBanner('xxl')}');
        ">

<ul class="list thick">
    <li class="block large">
        {if="!$contact->isMe()"}
            <span class="control icon active white" onclick="ContactActions_ajaxChat('{$contact->id|echapJS}')"
                title="{$c->__('button.chat')}">
                <i class="material-icons">comment</i>
            </span>
        {/if}
        {if="$roster"}
            <span class="control icon active white divided" onclick="ContactHeader_ajaxEditContact('{$contact->id|echapJS}')"
                title="{$c->__('button.edit')}">
                <i class="material-icons">edit</i>
            </span>
            <span class="control icon active white" onclick="ContactHeader_ajaxDeleteContact('{$contact->id|echapJS}')"
                title="{$c->__('button.delete')}">
                <i class="material-icons">delete</i>
            </span>
        {else}
            {if="$contact->isMe()"}
                <span class="control icon active white divided" onclick="MovimUtils.reload('{$c->route('conf')}')"
                    title="{$c->__('button.edit')}">
                    <i class="material-icons">tune</i>
                </span>
            {else}
                <span class="control icon active white divided" onclick="ContactActions_ajaxAddAsk('{$contact->id|echapJS}')"
                    title="{$c->__('button.add')}">
                    <i class="material-icons">person_add</i>
                </span>
            {/if}
        {/if}
        <span class="primary icon active white" onclick="history.back()">
            <i class="material-icons">arrow_back</i>
        </span>
        <span class="primary icon bubble active" onclick="ContactActions_ajaxGetDrawer('{$contact->id|echapJS}')">
            <img src="{if="$roster"}{$roster->getPhoto('m')}{else}{$contact->getPhoto('m')}{/if}">
        </span>
        <div>
            <p class="line active" onclick="ContactActions_ajaxGetDrawer('{$contact->id|echapJS}')">
                {$contact->truename}
                {if="$roster && $roster->group"}
                    <span class="tag color {$roster->group|stringToColor}">{$roster->group}</span>
                {/if}
            </p>
            <p class="line active" onclick="ContactActions_ajaxGetDrawer('{$contact->id|echapJS}')">
                {if="$roster && $roster->name && $roster->name != $contact->truename"}
                    {$roster->name} ·
                {/if}
                {$contact->id}
            </p>
        </div>
    </li>
</ul>
</header>