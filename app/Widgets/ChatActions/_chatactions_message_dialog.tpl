<section id="chat_actions">
    <ul class="list" id="message_preview">
        {autoescape="off"}
            {$c->prepareMessage($message)}
        {/autoescape}
        <li class="reactions">
            <div>
                <p>{loop="$message->reactions->groupBy('emoji')"}<a class="chip" href="#" title="{$value->implode('truename', ', ')}">{autoescape="off"}{$key|addEmojis:true}{/autoescape}{$value->implode('truename', ', ')}</a>{/loop}</p>
            </div>
        </li>
    </ul>
    <ul class="list divided active middle">
        {if="!$message->encrypted"}
            <li onclick="Stickers_ajaxReaction({$message->mid}); Dialog_ajaxClear()">
                <span class="primary icon gray">
                    <i class="material-symbols">add_reaction</i>
                </span>
                <div>
                    <p class="normal">{$c->__('message.react')}</p>
                </div>
            </li>
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
        <li onclick="Chat_ajaxHttpDaemonReply({$message->mid}); Dialog_ajaxClear()">
            <span class="primary icon gray">
                <i class="material-symbols">reply</i>
            </span>
            <div>
                <p class="normal">{$c->__('button.reply')}</p>
            </div>
        </li>

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

        {if="$conference && $conference->presence && $conference->presence->mucrole == 'moderator' && $conference->info && $conference->info->hasModeration()"}
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
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
</footer>
