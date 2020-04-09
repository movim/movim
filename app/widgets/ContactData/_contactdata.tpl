<br />

{$url = $contact->getPhoto('l')}
{if="$url"}
<ul class="list middle">
    <li>
        <content>
            <p class="center">
                <img class="avatar" src="{$url}">
            </p>
        </content>
    </li>
</ul>
{/if}

<div class="block">
    <ul class="list middle">
        <li>
            <content>
                <p class="normal center	">
                    {$contact->truename}
                    {if="isset($roster) && isset($roster->presence)"}
                        ·  {$roster->presence->presencetext}
                    {/if}
                </p>
                {if="$contact->email"}
                    <p class="center"><a href="mailto:{$contact->email}">{$contact->email}</a></p>
                {/if}
                {if="$contact->description != null && trim($contact->description) != ''"}
                    <p class="center all" title="{$contact->description}">
                        {autoescape="off"}
                            {$contact->description|nl2br}
                        {/autoescape}
                    </p>
                {/if}
            </content>
        </li>
        <!--<li>
            <span class="primary icon gray">
                <i class="material-icons">accounts</i>
            </span>
            <p class="normal">{$c->__('communitydata.sub', 0)}</p>
        </li>-->
    </ul>

    {if="$contact->url != null"}
        <ul class="list thin">
            <li>
                <span class="primary icon gray"><i class="material-icons">link</i></span>
                <content>
                    <p class="normal line">
                        {if="filter_var($contact->url, FILTER_VALIDATE_URL)"}
                            <a href="{$contact->url}" target="_blank">{$contact->url}</a>
                        {else}
                            {$contact->url}
                        {/if}
                    </p>
                </content>
            </li>
        </ul>
    {/if}

    {if="$contact->adrlocality != null || $contact->adrcountry != null"}
        <ul class="list middle">
            <li>
                <span class="primary icon gray"><i class="material-icons">location_city</i></span>
                <content>
                    {if="$contact->adrlocality != null"}
                        <p class="normal">{$contact->adrlocality}</p>
                    {/if}
                    {if="$contact->adrcountry != null"}
                        <p {if="$contact->adrlocality == null"}class="normal"{/if}>
                            {$contact->adrcountry}
                        </p>
                    {/if}
                </content>
            </li>
        </ul>
    {/if}
</div>
<div class="block">
    <ul class="list middle active divided spaced">
        {if="!$contact->isMe()"}
            {if="$roster && $roster->presence && $roster->presence->capability && $roster->presence->capability->isJingle()"}
                <li onclick="VisioLink.openVisio('{$roster->presence->jid . '/' . $roster->presence->resource}');">
                    <span class="primary icon green">
                        <i class="material-icons">phone</i>
                    </span>
                    <content>
                        <p class="normal">{$c->__('button.call')}</p>
                    </content>
                </li>
            {/if}
            <li onclick="ContactHeader_ajaxChat('{$contact->jid|echapJS}')">
                <span class="primary icon gray">
                    <i class="material-icons">comment</i>
                </span>
                <content>
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
                            <p><i class="material-icons">lock</i> {$c->__('message.encrypted')}</p>
                        {elseif="stripTags($message->body) != ''"}
                            <p class="line">{$message->body|stripTags}</p>
                        {/if}
                    {/if}
                </content>
            </li>
        {/if}
        {if="$roster && !in_array($roster->subscription, ['', 'both'])"}
            <li>
                {if="$roster->subscription == 'to'"}
                    <span class="primary icon gray">
                        <i class="material-icons">arrow_upward</i>
                    </span>
                    <content>
                        <p>{$c->__('subscription.to')}</p>
                        <p>{$c->__('subscription.to_text')}</p>
                        <p>
                            <button class="button flat" onclick="ContactActions_ajaxAddAsk('{$contact->id|echapJS}')">
                                {$c->__('subscription.to_button')}
                            </button>
                        </p>
                    </content>
                {/if}
                {if="$roster->subscription == 'from'"}
                    <span class="primary icon gray">
                        <i class="material-icons">arrow_downward</i>
                    </span>
                    <content>
                        <p>{$c->__('subscription.from')}</p>
                        <p>{$c->__('subscription.from_text')}</p>
                        <p>
                            <button class="button flat" onclick="ContactActions_ajaxAddAsk('{$contact->id|echapJS}')">
                                {$c->__('subscription.from_button')}
                            </button>
                        </p>
                    </content>
                {/if}
                {if="$roster->subscription == 'none'"}
                    <span class="primary icon gray">
                        <i class="material-icons">block</i>
                    </span>
                    <content>
                        <p>{$c->__('subscription.nil')}</p>
                        <p>{$c->__('subscription.nil_text')}</p>
                        <p>
                            <button class="button flat" onclick="ContactActions_ajaxAddAsk('{$contact->id|echapJS}')">
                                {$c->__('subscription.nil_button')}
                            </button>
                        </p>
                    </content>
                {/if}
            </li>
        {/if}
        <a href="{$contact->getBlogUrl()}" target="_blank" class="block large simple">
            <li>
                <span class="primary icon">
                    <i class="material-icons">wifi_tethering</i>
                </span>
                <span class="control icon">
                    <i class="material-icons">chevron_right</i>
                </span>
                <content>
                    <p></p>
                    <p class="normal">{$c->__('blog.visit')}</p>
                </content>
            </li>
        </a>
    </ul>
</div>

{if="count($subscriptions) > 0"}
    <ul class="list active large">
        <li class="subheader large">
            <content>
                <p>
                    <span class="info">{$subscriptions|count}</span>
                    {$c->__('page.communities')}
                </p>
            </content>
        </li>
        {loop="$subscriptions"}
            <a href="{$c->route('community', [$value->server, $value->node])}">
                <li title="{$value->server} - {$value->node}">
                    {if="$value->info"}
                        {$url = $value->info->getPhoto('m')}
                    {/if}

                    {if="$url"}
                        <span class="primary icon bubble">
                            <img src="{$url}"/>
                        </span>
                    {else}
                        <span class="primary icon bubble color {$value->node|stringToColor}">
                            {$value->node|firstLetterCapitalize}
                        </span>
                    {/if}
                    <span class="control icon gray">
                        <i class="material-icons">chevron_right</i>
                    </span>
                    <content>
                        <p class="line normal">
                            {if="$value->info && $value->info->name"}
                                {$value->info->name}
                            {elseif="$value->name"}
                                {$value->name}
                            {else}
                                {$value->node}
                            {/if}
                        </p>
                        {if="$value->info && $value->info->description"}
                            <p class="line">{$value->info->description|strip_tags}</p>
                        {/if}
                    </content>
                </li>
            </a>
        {/loop}
    </ul>
{/if}
