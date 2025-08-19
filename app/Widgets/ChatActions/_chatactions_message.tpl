<li {if="$message->isMine()"}class="oppose"{/if} onclick="Chat_ajaxGetMessageContext('{$message->jid}', {$message->mid}); Drawer.clear()">
    <div class="bubble {if="$message->file && $message->file->isPicture"}file{/if}"
        data-publishedprepared="{if="$search"}{$message->published|prepareDate}{else}{$message->published|prepareTime}{/if}">
        <div class="message">
            {if="$message->isMuc()"}
                <span class="resource {$message->resolveColor()}">{$message->resource}</span>
            {/if}
            {if="$message->encrypted"}
                <p class="encrypted">{if="$message->retracted"}{$c->__('message.retracted')}{else}{$c->__('message.encrypted')}{/if} <i class="material-symbols fill">lock</i></p>
            {elseif="$message->retracted"}
                <p class="retracted">{$c->__('message.retracted')} <i class="material-symbols">delete</i></p>
            {elseif="$message->file && $message->file->isPicture"}
                <div class="file" data-type="{$message->file->type}">
                    <img src="{$message->file->url|protectPicture}">
                </div>
            {else}
                {if="$search"}
                   <p>{autoescape="off"}{$message->headline|trim|addEmojis}{/autoescape}</p>
                {else}
                   <p>{autoescape="off"}{$message->body|trim|addEmojis}{/autoescape}</p>
                {/if}
            {/if}
            <span class="info"></span>
        </div>
    </div>
</li>