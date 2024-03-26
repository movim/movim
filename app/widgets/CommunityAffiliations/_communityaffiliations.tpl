{if="$myaffiliation && $myaffiliation->affiliation == 'owner' && $info"}
    <ul class="list active">
        <li onclick="CommunityConfig_ajaxGetAvatar('{$info->server|echapJS}', '{$info->node|echapJS}')">
            <span class="primary icon gray">
                <i class="material-symbols">image</i>
            </span>
            <div>
                <p class="normal">{$c->__('page.avatar')}</p>
            </div>
        </li>
        <li onclick="CommunityConfig_ajaxGetConfig('{$info->server|echapJS}', '{$info->node|echapJS}')">
            <span class="primary icon gray">
                <i class="material-symbols">settings</i>
            </span>
            <div>
                <p class="normal">{$c->__('communityaffiliation.configuration')}</p>
            </div>
        </li>
        <li onclick="CommunityAffiliations_ajaxGetSubscriptions('{$info->server|echapJS}', '{$info->node|echapJS}', true)">
            <span class="primary icon gray">
                <i class="material-symbols">contacts</i>
            </span>
            <div>
                <p class="normal">{$c->__('communityaffiliation.subscriptions')}</p>
            </div>
        </li>
        <li onclick="CommunityAffiliations_ajaxAffiliations('{$info->server|echapJS}', '{$info->node|echapJS}')">
            <span class="primary icon gray">
                <i class="material-symbols">supervisor_account</i>
            </span>
            <div>
                <p class="normal">{$c->__('communityaffiliation.roles')}</p>
            </div>
        </li>
        <li onclick="CommunityAffiliations_ajaxDelete('{$info->server|echapJS}', '{$info->node|echapJS}')">
            <span class="primary icon gray">
                <i class="material-symbols">delete</i>
            </span>
            <div>
                <p class="normal">{$c->__('button.delete')}</p>
            </div>
        </li>
    </ul>
{/if}

{if="$affiliations->where('affiliation', 'owner')->isNotEmpty()"}
    <ul class="list card active">
        <li class="subheader">
            <div>
                <p>{$c->__('communityaffiliation.owners')}</p>
            </div>
        </li>
        {loop="$affiliations->where('affiliation', 'owner')"}
            {$contact = $c->getContact($value->jid)}
            <li title="{$contact->jid}"
                onclick="MovimUtils.reload('{$c->route('contact', $contact->jid)}')">
                <span class="primary icon bubble">
                    <img src="{$contact->getPicture('m')}">
                </span>
                <div>
                    <p>{$contact->truename}</p>
                    <p>{$contact->jid}</p>
                </div>
            </li>
        {/loop}
    </ul>
{/if}

{if="$affiliations->where('affiliation', 'publisher')->isNotEmpty()"}
<ul class="list card active">
    <li class="subheader">
        <div>
            <p>{$c->__('communityaffiliation.publishers')}</p>
        </div>
    </li>
    {loop="$affiliations->where('affiliation', 'publisher')"}
        {$contact = $c->getContact($value->jid)}
        <li title="{$contact->jid}"
            onclick="MovimUtils.reload('{$c->route('contact', $contact->jid)}')">
            <span class="primary icon bubble">
                <img src="{$contact->getPicture('m')}">
            </span>
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
                <i class="material-symbols">unfold_more</i>
            </span>
            <div>
                <p>{$c->__('button.more')}</p>
                <p>{$c->__('communitydata.sub', $allsubscriptionscount)}</p>
            </div>
        </li>
    </ul>
{/if}
