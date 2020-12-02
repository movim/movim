<br />

{$url = $contact->getPhoto('l')}
{if="$url"}
<ul class="list middle">
    <li>
        <div>
            <p class="center">
                <img class="avatar" src="{$url}">
            </p>
        </div>
    </li>
</ul>
{/if}

<div class="block">
    <ul class="list middle">
        <li>
            <div>
                <p class="normal center	">
                    {$contact->truename}
                    {if="isset($roster) && isset($roster->presence)"}
                        <span class="second">{$roster->presence->presencetext}</span>
                    {/if}
                </p>

                {if="$roster && $roster->presence && $roster->presence->seen"}
                    <p class="center">
                        <span class="second">
                            {$c->__('last.title')} {$roster->presence->seen|strtotime|prepareDate:true,true}
                        </span>
                    </p>
                {/if}
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
            </div>
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
                <div>
                    <p class="normal line">
                        {if="filter_var($contact->url, FILTER_VALIDATE_URL)"}
                            <a href="{$contact->url}" target="_blank">{$contact->url}</a>
                        {else}
                            {$contact->url}
                        {/if}
                    </p>
                </div>
            </li>
        </ul>
    {/if}

    {if="$contact->adrlocality != null || $contact->adrcountry != null"}
        <ul class="list middle">
            <li>
                <span class="primary icon gray"><i class="material-icons">location_city</i></span>
                <div>
                    {if="$contact->adrlocality != null"}
                        <p class="normal">{$contact->adrlocality}</p>
                    {/if}
                    {if="$contact->adrcountry != null"}
                        <p {if="$contact->adrlocality == null"}class="normal"{/if}>
                            {$contact->adrcountry}
                        </p>
                    {/if}
                </div>
            </li>
        </ul>
    {/if}
</div>
<div class="block">
    <ul class="list middle active divided spaced">
        {if="!$contact->isMe()"}

            {if="$roster && $roster->presences->count() > 0"}
                {loop="$roster->presences"}
                    {if="$value->capability && $value->capability->isJingleAudio()"}
                        <li onclick="VisioLink.openVisio('{$value->jid|echapJS}');">
                            <span class="primary icon green">
                                <i class="material-icons">phone</i>
                            </span>
                            <div>
                                <p class="normal">{$c->__('button.audio_call')}</p>
                            </div>
                        </li>
                    {/if}
                    {if="$value->capability && $value->capability->isJingleVideo()"}
                        <li onclick="VisioLink.openVisio('{$value->jid|echapJS}', '', true);">
                            <span class="primary icon green">
                                <i class="material-icons">videocam</i>
                            </span>
                            <div>
                                <p class="normal">{$c->__('button.video_call')}</p>
                            </div>
                        </li>
                        {break}
                    {/if}
                {/loop}
            {/if}
            <li onclick="ContactHeader_ajaxChat('{$contact->jid|echapJS}')">
                <span class="primary icon gray">
                    <i class="material-icons">comment</i>
                </span>
                <div>
                    <p class="normal">
                        {if="isset($message)"}
                            <span class="info" title="{$message->published|strtotime|prepareDate}">
                                {$message->published|strtotime|prepareDate:true,true}
                            </span>
                        {/if}
                        {$c->__('button.chat')}
                    </p>
                    {if="isset($message)"}
                        {if="$message->retracted"}
                            <p><i class="material-icons">delete</i> {$c->__('message.retracted')}</p>
                        {elseif="$message->encrypted"}
                            <p><i class="material-icons">lock</i> {$c->__('message.encrypted')}</p>
                        {elseif="$message->file"}
                            <p>
                                {if="$message->jidfrom == $message->user_id"}
                                    <span class="moderator">{$c->__('chats.me')}:</span>
                                {/if}
                                {if="typeIsPicture($message->file['type'])"}
                                    <i class="material-icons">image</i> {$c->__('chats.picture')}
                                {elseif="typeIsVideo($message->file['type'])"}
                                    <i class="material-icons">local_movies</i> {$c->__('chats.video')}
                                {else}
                                    <i class="material-icons">insert_drive_file</i> {$c->__('avatar.file')}
                                {/if}
                            </p>
                        {elseif="stripTags($message->body) != ''"}
                            <p>
                                {if="$message->jidfrom == $message->user_id"}
                                    <span class="moderator">{$c->__('chats.me')}:</span>
                                {/if}
                                {autoescape="off"}
                                    {$message->body|stripTags|addEmojis}
                                {/autoescape}
                            </p>
                        {/if}
                    {/if}
                </div>
            </li>
        {/if}
        {if="$roster && !in_array($roster->subscription, ['', 'both'])"}
            <li>
                {if="$roster->subscription == 'to'"}
                    <span class="primary icon gray">
                        <i class="material-icons">arrow_upward</i>
                    </span>
                    <div>
                        <p>{$c->__('subscription.to')}</p>
                        <p>{$c->__('subscription.to_text')}</p>
                        <p>
                            <button class="button flat" onclick="ContactActions_ajaxAddAsk('{$contact->id|echapJS}')">
                                {$c->__('subscription.to_button')}
                            </button>
                        </p>
                    </div>
                {/if}
                {if="$roster->subscription == 'from'"}
                    <span class="primary icon gray">
                        <i class="material-icons">arrow_downward</i>
                    </span>
                    <div>
                        <p>{$c->__('subscription.from')}</p>
                        <p>{$c->__('subscription.from_text')}</p>
                        <p>
                            <button class="button flat" onclick="ContactActions_ajaxAddAsk('{$contact->id|echapJS}')">
                                {$c->__('subscription.from_button')}
                            </button>
                        </p>
                    </div>
                {/if}
                {if="$roster->subscription == 'none'"}
                    <span class="primary icon gray">
                        <i class="material-icons">block</i>
                    </span>
                    <div>
                        <p>{$c->__('subscription.nil')}</p>
                        <p>{$c->__('subscription.nil_text')}</p>
                        <p>
                            <button class="button flat" onclick="ContactActions_ajaxAddAsk('{$contact->id|echapJS}')">
                                {$c->__('subscription.nil_button')}
                            </button>
                        </p>
                    </div>
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
                <div>
                    <p></p>
                    <p class="normal">{$c->__('blog.visit')}</p>
                </div>
            </li>
        </a>
    </ul>
</div>

{if="count($subscriptions) > 0"}
    <ul class="list active large">
        <li class="subheader large">
            <div>
                <p>
                    <span class="info">{$subscriptions|count}</span>
                    {$c->__('page.communities')}
                </p>
            </div>
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
                    <div>
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
                    </div>
                </li>
            </a>
        {/loop}
    </ul>
{/if}
