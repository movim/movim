<a
    class="avatar"
    style="background-image: url({$contact->getPhoto('l')}"
    href="{$c->route('friend',$contact->jid)}">
</a>
<h1 class="paddedbottom">{$contact->getTrueName()}</h1>
{if="isset($contact->url) && filter_var($contact->url, FILTER_VALIDATE_URL)"}
    <a target="_blank" class="paddedtopbottom url" href="{$contact->url}">{$contact->url}</a>
{/if}

{if="$contact->status"}
    <div class="status">
        {$contact->status|prepareString}
    </div>
{/if}
