<div data-mid="{$message->mid}">
    <ul class="list">
        <li>
            <span class="primary icon">
                <i class="material-icons">reply</i>
            </span>
            <span class="control icon active" onclick="Chat_ajaxClearReply()">
                <i class="material-icons">close</i>
            </span>
            <div class="parent">
                {if="$message->type == 'groupchat'"}
                    {$color = $message->resolveColor()}
                    <p class="line from">
                        <span class="resource {$color}">{$message->resource}</span>
                    </p>
                {elseif="$message->from"}
                    <p class="line from">{$message->from->truename}</p>
                {else}
                    <p class="line from">{$c->__('button.reply')}</p>
                {/if}
                {if="$message->file"}
                    <p class="line">
                        {if="typeIsPicture($message->file['type'])"}
                            <i class="material-icons">image</i> {$c->__('chats.picture')}
                        {elseif="typeIsVideo($message->file['type'])"}
                            <i class="material-icons">local_movies</i> {$c->__('chats.video')}
                        {else}
                            <i class="material-icons">insert_drive_file</i> {$c->__('avatar.file')}
                        {/if}
                    </p>
                {elseif="$message->encrypted"}
                    <p class="line"><i class="material-icons">lock</i> {$c->__('message.encrypted')}</p>
                {else}
                    <p class="line">{$message->body}</p>
                {/if}
            </div>
        </li>
    </ul>
</div>