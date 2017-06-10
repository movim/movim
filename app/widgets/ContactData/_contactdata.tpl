<br />
{$url = $contact->getPhoto('s')}

<div class="block">
    <ul class="list middle">
        <li>
            {if="$url"}
                <span class="primary icon bubble
                    {if="isset($presence)"}status {$presence}{/if}">
                    <img src="{$url}">
                </span>
            {else}
                <span class="primary icon bubble color {$contact->jid|stringToColor}
                     {if="isset($presence)"}status {$presence}{/if}
                ">
                    {$contact->getTrueName()|firstLetterCapitalize}
                </span>
            {/if}
            <span class="control active icon gray" onclick="ContactActions_ajaxGetDrawer('{$contact->jid}')">
                <i class="zmdi zmdi-more"></i>
            </span>
            <p class="normal">
                {$contact->getTrueName()}
            </p>
            {if="$contact->email != null"}
                <p><img src="{$contact->getPhoto('email')}"/></p>
            {/if}
            {if="$contact->description != null && trim($contact->description) != ''"}
                <p title="{$contact->description}">{$contact->description}</p>
            {/if}
        </li>
        {$sub = $contact->countSubscribers()}

        {if="$sub > 0"}
        <li>
            <span class="primary icon gray">
                <i class="zmdi zmdi-accounts"></i>
            </span>
            <p class="normal">{$c->__('communitydata.sub', $sub)}</p>
        </li>
        {/if}
    </ul>

    {if="$contact->url != null"}
        <ul class="list thin">
            <li>
                <span class="primary icon gray"><i class="zmdi zmdi-link"></i></span>
                <p class="normal line">
                    {if="filter_var($contact->url, FILTER_VALIDATE_URL)"}
                        <a href="{$contact->url}" target="_blank">{$contact->url}</a>
                    {else}
                        {$contact->url}
                    {/if}
                </p>
            </li>
        </ul>
    {/if}

    {if="$contact->adrlocality != null || $contact->adrcountry != null"}
        <ul class="list middle">
            <li>
                <span class="primary icon gray"><i class="zmdi zmdi-pin"></i></span>
                {if="$contact->adrlocality != null"}
                    <p class="normal">{$contact->adrlocality}</p>
                {/if}
                {if="$contact->adrcountry != null"}
                    <p {if="$contact->adrlocality == null"}class="normal"{/if}>
                        {$contact->adrcountry}
                    </p>
                {/if}
            </li>
        </ul>
    {/if}
</div>
<div class="block">
    <ul class="list middle active divided spaced">
        {if="!$contact->isMe()"}
            {if="isset($caps) && $caps->isJingle()"}
                <li onclick="VisioLink.openVisio('{$contactr->getFullResource()}');">
                    <span class="primary icon green">
                        <i class="zmdi zmdi-phone"></i>
                    </span>
                    <p class="normal">{$c->__('button.call')}</p>
                </li>
            {/if}
            <li onclick="ContactHeader_ajaxChat('{$contact->jid|echapJS}')">
                <span class="primary icon gray">
                    <i class="zmdi zmdi-comment-text-alt"></i>
                </span>
                <p class="normal">
                    {if="isset($message)"}
                        <span class="info" title="{$message->published|strtotime|prepareDate}">
                            {$message->published|strtotime|prepareDate:true,true}
                        </span>
                    {/if}
                    {$c->__('button.chat')}
                </p>
                {if="isset($message)"}
                    {if="preg_match('#^\?OTR#', $message->body)"}
                        <p><i class="zmdi zmdi-lock"></i> {$c->__('message.encrypted')}</p>
                    {elseif="stripTags($message->body) != ''"}
                        <p class="line">{$message->body|stripTags}</p>
                    {/if}
                {/if}
            </li>
        {/if}
        <a href="{$c->route('blog', $contact->jid)}" target="_blank" class="block large simple">
            <li>
                <span class="primary icon">
                    <i class="zmdi zmdi-portable-wifi"></i>
                </span>
                <span class="control icon">
                    <i class="zmdi zmdi-chevron-right"></i>
                </span>
                <p></p>
                <p class="normal">{$c->__('blog.visit')}</p>
            </li>
        </a>
        {if="$contactr && $contactr->rostersubscription != 'both'"}
            <li>
                {if="$contactr->rostersubscription == 'to'"}
                    <span class="primary icon gray">
                        <i class="zmdi zmdi-arrow-in"></i>
                    </span>
                    <p>{$c->__('subscription.to')}</p>
                    <p>{$c->__('subscription.to_text')}</p>
                    <p>
                        <button class="button flat" onclick="ContactData_ajaxAccept('{$contactr->jid}')">
                            {$c->__('subscription.to_button')}
                        </button>
                    </p>
                {/if}
                {if="$contactr->rostersubscription == 'from'"}
                    <span class="primary icon gray">
                        <i class="zmdi zmdi-arrow-out"></i>
                    </span>
                    <p>{$c->__('subscription.from')}</p>
                    <p>{$c->__('subscription.from_text')}</p>
                    <p>
                        <button class="button flat" onclick="ContactData_ajaxAccept('{$contactr->jid}')">
                            {$c->__('subscription.from_button')}
                        </button>
                    </p>
                {/if}
                {if="$contactr->rostersubscription == 'none'"}
                    <span class="primary icon gray">
                        <i class="zmdi zmdi-block"></i>
                    </span>

                    <p>{$c->__('subscription.nil')}</p>
                    <p>{$c->__('subscription.nil_text')}</p>
                    <p>
                        <button class="button flat" onclick="ContactData_ajaxAccept('{$contactr->jid}')">
                            {$c->__('subscription.nil_button')}
                        </button>
                    </p>
                {/if}
            </li>
        {/if}
    </ul>
</div>

{if="count($subscriptions) > 0"}
    <ul class="list active large">
        <li class="subheader large">
            <p>
                <span class="info">{$subscriptions|count}</span>
                {$c->__('page.communities')}
            </p>
        </li>
        {loop="$subscriptions"}
            <a href="{$c->route('community', [$value->server, $value->node])}">
                <li title="{$value->server} - {$value->node}">
                    {if="$value->logo"}
                        <span class="primary icon bubble">
                            <img src="{$value->getLogo(50)}">
                        </span>
                    {else}
                        <span class="primary icon bubble color {$value->node|stringToColor}">{$value->node|firstLetterCapitalize}</span>
                    {/if}
                    <span class="control icon gray">
                        <i class="zmdi zmdi-chevron-right"></i>
                    </span>
                    <p class="line normal">
                        {if="$value->name"}
                            {$value->name}
                        {else}
                            {$value->node}
                        {/if}
                    </p>
                    {if="$value->description"}
                        <p class="line">{$value->description|strip_tags}</p>
                    {/if}
                </li>
            </a>
        {/loop}
    </ul>
{/if}

