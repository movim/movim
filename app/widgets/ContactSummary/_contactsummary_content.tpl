<div class="profile"
    {if="$contact->loclatitude"}
        data-lat="{$contact->loclatitude}"
        data-lon="{$contact->loclongitude}"
        data-avatar="{$contact->getPhoto('s')}"
        data-date="{$contact->loctimestamp|strtotime|prepareDate}"
    {/if}>
    <a
        class="avatar"
        style="background-image: url({$contact->getPhoto('l')});"
        href="{$c->route('friend',$contact->jid)}">
    </a>
    <h1 class="paddedbottom">{$contact->getTrueName()}</h1>

    {if="$contact->status"}
        <div class="status">
            {$contact->status|prepareString}
        </div>
    {/if}
</div>
