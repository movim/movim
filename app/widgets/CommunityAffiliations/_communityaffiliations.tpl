{if="$role == 'owner'"}
    <ul class="list active">
        <li onclick="CommunityConfig_ajaxGetConfig('{$item->server|echapJS}', '{$item->node|echapJS}')">
            <span class="primary icon gray">
                <i class="zmdi zmdi-settings"></i>
            </span>
            <p class="normal">{$c->__('communityaffiliation.configuration')}</p>
        </li>
        <li onclick="CommunityAffiliations_ajaxGetSubscriptions('{$item->server|echapJS}', '{$item->node|echapJS}', true)">
            <span class="primary icon gray">
                <i class="zmdi zmdi-accounts-list"></i>
            </span>
            <p class="normal">{$c->__('communityaffiliation.subscriptions')}</p>
        </li>
        <li onclick="CommunityAffiliations_ajaxDelete('{$item->server|echapJS}', '{$item->node|echapJS}')">
            <span class="primary icon gray">
                <i class="zmdi zmdi-delete"></i>
            </span>
            <p class="normal">{$c->__('button.delete')}</p>
        </li>
    </ul>
{/if}

{loop="$affiliations"}
<ul class="list card">
    <li class="subheader">
        <p>{$c->__('communityaffiliation.owner')}</p>
    </li>
    {if="$value[1] == 'owner'"}
        {$contact = $c->getContact($value[0])}
        <li>
            {$url = $contact->getPhoto('m')}
            {if="$url"}
                <span class="primary icon bubble"
                    style="background-image: url({$url});">
                </span>
            {else}
                <span class="primary icon bubble color {$contact->jid|stringToColor}">
                    {$contact->getTrueName()|firstLetterCapitalize}
                </span>
            {/if}
            <p class="normal">{$contact->getTrueName()}</p>
        </li>
    {/if}
</ul>
{/loop}
