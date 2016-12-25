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
        <li onclick="CommunityAffiliations_ajaxAffiliations('{$item->server|echapJS}', '{$item->node|echapJS}')">
            <span class="primary icon gray">
                <i class="zmdi zmdi-account-box-o"></i>
            </span>
            <p class="normal">{$c->__('communityaffiliation.roles')}</p>
        </li>
        <li onclick="CommunityAffiliations_ajaxDelete('{$item->server|echapJS}', '{$item->node|echapJS}')">
            <span class="primary icon gray">
                <i class="zmdi zmdi-delete"></i>
            </span>
            <p class="normal">{$c->__('button.delete')}</p>
        </li>
    </ul>
{/if}

{if="array_key_exists('owner', $affiliations)"}
    <ul class="list card active">
        <li class="subheader">
            <p>{$c->__('communityaffiliation.owners')}</p>
        </li>
        {loop="$affiliations['owner']"}
            {$contact = $c->getContact($value['jid'])}
            <li title="{$contact->jid}"
                onclick="MovimUtils.reload('{$c->route('contact', $contact->jid)}')">
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
                <p>{$contact->getTrueName()}</p>
                <p>{$contact->jid}</p>
            </li>
        {/loop}
    </ul>
{/if}

{if="array_key_exists('publisher', $affiliations)"}
<ul class="list card active">
    <li class="subheader">
        <p>{$c->__('communityaffiliation.publishers')}</p>
    </li>
    {loop="$affiliations['publisher']"}
        {$contact = $c->getContact($value['jid'])}
        <li title="{$contact->jid}"
            onclick="MovimUtils.reload('{$c->route('contact', $contact->jid)}')">
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
            <p>{$contact->getTrueName()}</p>
            <p>{$contact->jid}</p>
        </li>
    {/loop}
</ul>
{/if}
