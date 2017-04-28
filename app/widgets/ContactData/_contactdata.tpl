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
                     {if="isset($presence)"}status {$presence}{/if}"
                ">
                    {$contact->getTrueName()|firstLetterCapitalize}
                </span>
            {/if}
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
    </ul>
    <ul class="list thin">
        {if="$contact->url != null"}
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
        {/if}

        {if="$contact->adrlocality != null || $contact->adrcountry != null"}
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
        {/if}
    </ul>
    <ul class="list thin active">
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
                <p class="normal">{$c->__('button.chat')}</p>
            </li>
    </ul>
</div>

{if="count($subscriptions) > 0"}
    <ul class="list active middle large">
        <li class="subheader large">
            <p>
                <span class="info">{$subscriptions|count}</span>
                {$c->__('page.communities')}
            </p>
        </li>
        {loop="$subscriptions"}
            <li class="block"
                title="{$value->server} - {$value->node}"
                onclick="MovimUtils.redirect('{$c->route('community', [$value->server, $value->node])}')">
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
        {/loop}
    </ul>
{/if}

