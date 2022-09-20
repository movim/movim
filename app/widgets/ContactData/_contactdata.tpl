<br />

{autoescape="off"}
    {$c->prepareCard($contact, $roster)}
{/autoescape}

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
                    <i class="material-icons">open_in_new</i>
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
