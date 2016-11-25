<li
    id="{$contact->jid|cleanupId}"
    title="{$contact->jid}"
    name="{$contact->getSearchTerms()}"
    class="{if="$contact->value == null"}faded{/if}"
    onclick="
        Contact_ajaxGetContact('{$contact->jid}');
        Contact_ajaxRefreshFeed('{$contact->jid}');
    ">
    {$url = $contact->getPhoto('m')}
    {if="$url"}
        <span class="primary icon bubble
            {if="$contact->value"}
                status {$presencestxt[$contact->value]}
            {/if}"
            style="background-image: url({$url});">
        </span>
    {else}
        <span class="primary icon bubble color {$contact->jid|stringToColor}
            {if="$contact->value"}
                status {$presencestxt[$contact->value]}
            {/if}"
        ">
            {$contact->getTrueName()|firstLetterCapitalize}
        </span>
    {/if}
    {if="$contact->rostersubscription != 'both'"}
    <span class="control icon gray">
        {if="$contact->rostersubscription == 'to'"}
            <i class="zmdi zmdi-arrow-in"></i>
        {elseif="$contact->rostersubscription == 'from'"}
            <i class="zmdi zmdi-arrow-out"></i>
        {else}
            <i class="zmdi zmdi-block"></i>
        {/if}
    </span>
    {/if}
    <p class="normal line">{$contact->getTrueName()}</p>
    {if="$contact->groupname"}
    <p>
        <span class="tag color {$contact->groupname|stringToColor}">
            {$contact->groupname}
        </span>
    </p>
    {/if}
</li>
