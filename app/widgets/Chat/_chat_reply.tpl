<div data-mid="{$message->mid}">
    <ul class="list">
        <li>
            <span class="primary icon">
                <i class="material-symbols">reply</i>
            </span>
            <span class="control icon active" onclick="Chat_ajaxClearReply()">
                <i class="material-symbols">close</i>
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
                            <i class="material-symbols">image</i> {$c->__('chats.picture')}
                        {elseif="typeIsAudio($message->file['type'])"}
                            <i class="material-symbols">equalizer</i> {$c->__('chats.audio')}
                        {elseif="typeIsVideo($message->file['type'])"}
                            <i class="material-symbols">local_movies</i> {$c->__('chats.video')}
                        {else}
                            <i class="material-symbols">insert_drive_file</i> {$c->__('avatar.file')}
                        {/if}
                    </p>
                {elseif="$message->encrypted"}
                    <p class="line"><i class="material-symbols">lock</i> {$c->__('message.encrypted')}</p>
                {else}
                    <p class="line">{$message->body}</p>
                {/if}
            </div>
        </li>
    </ul>
</div>