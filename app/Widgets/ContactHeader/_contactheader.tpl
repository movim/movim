{if="$contact->isContact($c->me->id)"}
    <a href="{$c->route('publish')}" class="button action color" title="{$c->__('menu.add_post')}">
        <i class="material-symbols">post_add</i>
    </a>
{/if}

<header class="big top color {$contact->color}"
        style="
                background-image:
                linear-gradient(to top, rgba(23,23,23,0.9) 0, rgba(23,23,23,0.6) 5rem, rgba(23,23,23,0) 12rem), url('{$contact->getBanner(\Movim\ImageSize::XXL)}');
        ">

<ul class="list thick">
    <li class="block large">
        <span class="primary icon bubble active" onclick="ContactActions_ajaxGetDrawer('{$contact->id|echapJS}')">
            <img src="{if="$roster"}{$roster->getPicture(\Movim\ImageSize::M)}{else}{$contact->getPicture(\Movim\ImageSize::M)}{/if}">
        </span>
        {if="!$contact->isContact($c->me->id)"}
            <span class="control icon active white" onclick="ContactActions_ajaxChat('{$contact->id|echapJS}')"
                title="{$c->__('button.chat')}">
                <i class="material-symbols">comment</i>
            </span>
        {/if}
        {if="$roster"}
            <span class="control icon active white divided" onclick="ContactHeader_ajaxEditContact('{$contact->id|echapJS}')"
                title="{$c->__('button.edit')}">
                <i class="material-symbols">edit</i>
            </span>
            <span class="control icon active white" onclick="Notifications_ajaxDeleteContact('{$contact->id|echapJS}')"
                title="{$c->__('button.delete')}">
                <i class="material-symbols">delete</i>
            </span>
        {else}
            {if="$contact->isContact($c->me->id)"}
                <span class="control icon active white divided" onclick="MovimUtils.reload('{$c->route('configuration')}')"
                    title="{$c->__('button.edit')}">
                    <i class="material-symbols">tune</i>
                </span>
            {else}
                <span class="control icon active white divided" onclick="Notifications_ajaxAddAsk('{$contact->id|echapJS}')"
                    title="{$c->__('button.add')}">
                    <i class="material-symbols">person_add</i>
                </span>
            {/if}
        {/if}
        <div>
            <p class="line active" onclick="ContactActions_ajaxGetDrawer('{$contact->id|echapJS}')">
                {$contact->truename}
                {if="$contact->isBlocked()"}
                    <span class="tag color red">{$c->__('blocked.title')}</span>
                {/if}
                {if="$roster && $roster->group"}
                    <span class="tag color {$roster->group|stringToColor}">{$roster->group}</span>
                {/if}
            </p>
            <p class="line active" onclick="ContactActions_ajaxGetDrawer('{$contact->id|echapJS}')">
                {if="$roster && $roster->name && $roster->name != $contact->truename"}
                    {$roster->name} •
                {/if}
                {$contact->id}
            </p>
        </div>
    </li>
</ul>
</header>
