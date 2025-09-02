<li
    id="{$contact->jid|slugify|cleanupId}-chat-item"
    data-jid="{$contact->jid}"
    class="
        {if="$roster"}
            roster
            {if="$roster->presence"}
                {if="$roster->presence->value > 4"}faded{/if}
                {if="$roster->presence->last > 60"} inactive{/if}
                {if="$roster->presence->capability && $roster->presence->capability->identities()->first() && in_array($roster->presence->capability->identities()->first()->type, ['handheld', 'phone', 'web'])"}
                    action
                {/if}
            {/if}
        {/if}
        "
    title="{$contact->jid}{if="isset($message)"} • {$message->published|prepareDate}{/if}">
    <span class="primary icon bubble
        {if="$roster"}
            {if="$roster->presence"}status {$roster->presence->presencekey}{/if}
            {if="$roster->stories->count() > 0"}stories {if="$roster->storiesSeen"}seen{/if}{/if}
        {/if}
        {if="$contact->locationDistance"} location{/if}
    "
    {if="$roster && $roster->firstUnseenStory"}
        onclick="StoriesViewer_ajaxHttpGet({$roster->firstUnseenStory->id})"
    {/if}
    {if="$count > 0"}data-counter="{$count}"{/if}
    >
        <img src="{if="$roster"}{$roster->getPicture(\Movim\ImageSize::O)}{else}{$contact->getPicture(\Movim\ImageSize::O)}{/if}">
    </span>

    <div>
        <p class="normal line">
            {if="isset($message)"}
                <span class="info">
                    {$message->published|prepareDate:true,true}
                </span>
            {/if}
            {if="isset($message)"}
                <span class="info" title="{$message->published|prepareDate}">
                    {if="$message->jidfrom == $message->user_id"}
                        {if="$message->displayed"}
                            <span class="material-symbols">done_all</span>
                        {elseif="$message->delivered"}
                            <span class="material-symbols">check</span>
                        {/if}
                        &nbsp;
                    {/if}
                </span>
            {/if}
            {if="$roster"}
                {$roster->truename}
            {elseif="strpos($contact->jid, '/') != false"}
                {$exploded = explodeJid($contact->jid)}
                {$exploded.resource}
                <span class="second" title="{$exploded.jid}">
                    {$exploded.jid}
                </span>
            {else}
                {$contact->truename}
            {/if}

            {if="$roster && $roster->presence && $roster->presence->capability"}
                <span class="second" title="{$roster->presence->capability->name}">
                    <i class="material-symbols">{$roster->presence->capability->getDeviceIcon()}</i>
                </span>
            {/if}
        </p>
        {if="$status"}
            <p class="line">{$status}</p>
        {elseif="isset($message)"}
            <p class="line">
            {if="$contactincall"}
                <i class="material-symbols icon green blink">phone_in_talk</i>
                {$c->__('visio.in_call')} •
            {/if}
            {if="$message->encrypted"}
                <i class="material-symbols fill">lock</i> {if="$message->retracted"}{$c->__('message.retracted')}{else}{$c->__('message.encrypted')}{/if}
            {elseif="$message->retracted"}
                <i class="material-symbols">delete</i> {$c->__('message.retracted')}
            {elseif="$message->type == 'invitation'"}
                <i class="material-symbols icon gray">outgoing_mail</i> {$c->__('message.invitation')}
            {elseif="$message->type == 'jingle_incoming'"}
                <i class="material-symbols icon green">call</i> {$c->__('chat.jingle_incoming')}
            {elseif="$message->type == 'jingle_retract'"}
                <i class="material-symbols icon gray">phone_missed</i> {$c->__('chat.jingle_retract')}
            {elseif="$message->type == 'jingle_reject'"}
                <i class="material-symbols icon orange">phone_missed</i> {$c->__('chat.jingle_reject')}
            {elseif="$message->type == 'jingle_finish'"}
                <i class="material-symbols icon red">phone_disabled</i> {$c->__('chat.jingle_end')}
            {elseif="$message->type == 'jingle_outgoing'"}
                <i class="material-symbols icon blue">call</i> {$c->__('chat.jingle_outgoing')}
            {elseif="$message->type == 'jingle_end'"}
                <i class="material-symbols icon red">call_end</i> {$c->__('chat.jingle_end')}
            {elseif="$message->file"}
                {if="$message->jidfrom == $message->user_id"}
                    <span class="moderator">{$c->__('chats.me')}:</span>
                {/if}
                {if="$message->file->isPicture"}
                    {if="$message->file->preview && $message->file->preview['thumbnail_type'] == 'image/thumbhash' && $message->file->preview['thumbnail_url']"}
                        <img class="tinythumb" data-thumbhash="{$message->file->preview['thumbnail_url']}">
                    {else}
                        <i class="material-symbols">image</i>
                    {/if}
                    {$c->__('chats.picture')}
                {elseif="$message->file->isAudio"}
                    <i class="material-symbols">equalizer</i> {$c->__('chats.audio')}
                {elseif="$message->file->isVideo"}
                    <i class="material-symbols">local_movies</i> {$c->__('chats.video')}
                {else}
                    <i class="material-symbols">insert_drive_file</i> {$c->__('avatar.file')}
                {/if}
            {elseif="stripTags($message->body) != ''"}
                {if="$message->postid"}
                    <i class="material-symbols icon">article</i>
                {/if}
                {if="$message->jidfrom == $message->user_id"}
                    <span class="moderator">{$c->__('chats.me')}:</span>
                {/if}
                {if="$message->resolvedUrl && $message->resolvedUrl->cache"}
                    <i class="material-symbols">link</i>
                    {if="$message->resolvedUrl->cache->providerName"}
                        {$message->resolvedUrl->cache->providerName}
                        •
                    {/if}
                    {$message->resolvedUrl->cache->title}
                    {if="!empty($message->resolvedUrl->cache->description)"}
                        •
                        {$message->resolvedUrl->cache->description}
                    {/if}
                {else}
                {autoescape="off"}
                    {$message->inlinedBody|addEmojis}
                {/autoescape}
                {/if}
            {/if}
            </p>
        {elseif="$roster && $roster->presence && $roster && $roster->presence->status"}
            <p class="line">{$roster->presence->status}</p>
        {/if}
    </div>
</li>
