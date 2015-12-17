<li {if="$me"}class="oppose"{/if}>
    {$url = $contact->getPhoto('s')}
    {if="$url"}
        <span class="{if="$me"}control{else}primary{/if} icon bubble">
            {if="$me == null"}<a href="{$c->route('contact', $contact->jid)}">{/if}
                <img src="{$url}">
            {if="$me == null"}</a>{/if}
        </span>
    {else}
        <span class="{if="$me"}control{else}primary{/if} icon bubble color {$contact->jid|stringToColor}">
            {if="$me == null"}<a href="{$c->route('contact', $contact->jid)}">{/if}
                <i class="zmdi zmdi-account"></i>
            {if="$me == null"}</a>{/if}
        </span>
    {/if}

    <div class="bubble">
        <p></p>
        <span class="info"></span>
    </div>
</li>
