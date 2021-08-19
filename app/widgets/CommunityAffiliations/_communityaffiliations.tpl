{if="$role == 'owner' && $info"}
    <ul class="list active">
        <li onclick="CommunityConfig_ajaxGetAvatar('{$info->server|echapJS}', '{$info->node|echapJS}')">
            <span class="primary icon gray">
                <i class="material-icons">image</i>
            </span>
            <div>
                <p class="normal">{$c->__('page.avatar')}</p>
            </div>
        </li>
        <li onclick="CommunityConfig_ajaxGetConfig('{$info->server|echapJS}', '{$info->node|echapJS}')">
            <span class="primary icon gray">
                <i class="material-icons">settings</i>
            </span>
            <div>
                <p class="normal">{$c->__('communityaffiliation.configuration')}</p>
            </div>
        </li>
        <li onclick="CommunityAffiliations_ajaxGetSubscriptions('{$info->server|echapJS}', '{$info->node|echapJS}', true)">
            <span class="primary icon gray">
                <i class="material-icons">contacts</i>
            </span>
            <div>
                <p class="normal">{$c->__('communityaffiliation.subscriptions')}</p>
            </div>
        </li>
        <li onclick="CommunityAffiliations_ajaxAffiliations('{$info->server|echapJS}', '{$info->node|echapJS}')">
            <span class="primary icon gray">
                <i class="material-icons">supervisor_account</i>
            </span>
            <div>
                <p class="normal">{$c->__('communityaffiliation.roles')}</p>
            </div>
        </li>
        <li onclick="CommunityAffiliations_ajaxDelete('{$info->server|echapJS}', '{$info->node|echapJS}')">
            <span class="primary icon gray">
                <i class="material-icons">delete</i>
            </span>
            <div>
                <p class="normal">{$c->__('button.delete')}</p>
            </div>
        </li>
    </ul>
{/if}

{if="array_key_exists('owner', $affiliations)"}
    <ul class="list card active">
        <li class="subheader">
            <div>
                <p>{$c->__('communityaffiliation.owners')}</p>
            </div>
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
                        {$contact->truename|firstLetterCapitalize}
                    </span>
                {/if}
                <div>
                    <p>{$contact->truename}</p>
                    <p>{$contact->jid}</p>
                </div>
            </li>
        {/loop}
    </ul>
{/if}

{if="array_key_exists('publisher', $affiliations)"}
<ul class="list card active">
    <li class="subheader">
        <div>
            <p>{$c->__('communityaffiliation.publishers')}</p>
        </div>
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
                    {$contact->truename|firstLetterCapitalize}
                </span>
            {/if}
            <div>
                <p>{$contact->truename}</p>
                <p>{$contact->jid}</p>
            </div>
        </li>
    {/loop}
</ul>
{/if}

{if="$rostersubscriptions->isNotEmpty()"}
    {autoescape="off"}
        {$c->preparePublicSubscriptionsList($rostersubscriptions)}
    {/autoescape}
{elseif="$allsubscriptionscount > 0"}
    <ul class="list card active thin">
        <li class="subheader">
            <div>
                <p>{$c->__('communityaffiliation.public_subscriptions')}</p>
            </div>
        </li>
    </ul>
{/if}

{if="$allsubscriptionscount > 0"}
    <ul class="list active">
        <li onclick="CommunityAffiliations_ajaxShowFullPublicSubscriptionsList('{$server}', '{$node}')">
            <span class="primary icon gray">
                <i class="material-icons">unfold_more</i>
            </span>
            <div>
                <p>{$c->__('button.more')}</p>
                <p>{$c->__('communitydata.sub', $allsubscriptionscount)}</p>
            </div>
        </li>
    </ul>
{/if}