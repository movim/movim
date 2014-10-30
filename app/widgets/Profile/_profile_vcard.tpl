{if="isset($contact)"}
    <div class="card">
        <a href="{$c->route('friend', $contact->jid)}">
            <img src="{$contact->getPhoto('l')}"/>
            <h1 style="text-decoration: none;">{$contact->getTrueName()}</h1>
        </a>
        <a href="{$c->route('profile')}">
            <i class="fa fa-pencil"></i>
        </a>
        <div class="clear"></div>
    </div>
{else}
    <div class="not_yet">
        {$c->__('profile.not_yet')}<br /><br />
        <a 
            class="button color green icon add" 
            style="color: white;"
            href="{$c->route('profile')}">{$c->__('profile.create')}</a>
    </div>
{/if}
