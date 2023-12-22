<section id="chat_actions">
    <ul class="list" id="message_preview">
        <li {if="$message->isMine()"}class="oppose"{/if}>
            <div class="bubble {if="$message->picture"}file{/if}" data-publishedprepared="{$message->published|strtotime|prepareTime}">
                <div class="message">
                    {if="$message->retracted"}
                        <p class="retracted"><i class="material-symbols">delete</i>{$c->__('message.retracted')}</p>
                    {elseif="$message->picture"}
                        <div class="file" data-type="{$message->file['type']}">
                            <img src="{$message->file['uri']|protectPicture}">
                        </div>
                    {else}
                        <p>{autoescape="off"}{$message->body|trim|stripTags|addEmojis}{/autoescape}</p>
                    {/if}
                    <span class="info"></span>
                </div>
            </div>
        </li>
    </ul>
    <ul class="list divided active middle">
        <li onclick="Stickers_ajaxReaction({$message->mid})">
            <span class="primary icon gray">
                <i class="material-symbols">add_reaction</i>
            </span>
            <div>
                <p class="normal">{$c->__('message.react')}</p>
            </div>
        </li>
        <li onclick="Chat_ajaxHttpDaemonReply({$message->mid}); Dialog_ajaxClear()">
            <span class="primary icon gray">
                <i class="material-symbols">reply</i>
            </span>
            <div>
                <p class="normal">{$c->__('button.reply')}</p>
            </div>
        </li>
        {if="!$message->retracted"}
            <li
                onclick="MovimUtils.copyToClipboard(MovimUtils.decodeHTMLEntities(ChatActions.message.body)); ChatActions_ajaxCopiedMessageText(); Dialog_ajaxClear()">
                <span class="primary icon gray">
                    <i class="material-symbols">content_copy</i>
                </span>
                <div>
                    <p class="normal">{$c->__('chatactions.copy_text')}</p>
                </div>
            </li>
        {/if}

        {if="$message->isLast()"}
            <li onclick="Chat.editPrevious(); Dialog_ajaxClear();">
                <span class="primary icon gray">
                    <i class="material-symbols">edit</i>
                </span>
                <div>
                    <p class="normal">{$c->__('button.edit')}</p>
                </div>
            </li>
        {/if}

        {if="$message->isMine()"}
            <li onclick="ChatActions_ajaxHttpDaemonRetract({$message->mid})">
                <span class="primary icon gray">
                    <i class="material-symbols">delete</i>
                </span>
                <div>
                    <p class="normal">{$c->__('message.retract')}</p>
                </div>
            </li>
        {/if}

        {if="$conference && $conference->presence && $conference->presence->mucrole == 'moderator' && $conference->info && $conference->info->hasModeration() && !$message->retracted"}
            <li class="subheader">
                <div>
                    <p>{$c->__('chatroom.administration')}</p>
                </div>
            </li>
            <li onclick="ChatActions_ajaxHttpDaemonModerate({$message->mid})">
                <span class="primary icon gray">
                    <i class="material-symbols">delete</i>
                </span>
                <div>
                    <p class="normal">{$c->__('message.retract')}</p>
                </div>
            </li>
        {/if}
    </ul>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
</div>
