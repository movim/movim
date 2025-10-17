<li class="block">
    {if="$url->image"}
        <span class="primary icon thumb active color {$url->image|stringToColor}"
                onclick="Preview_ajaxHttpShow('{$url->image}')"
            style="background-image: url({$url->image|protectPicture})"
            >
            <i class="material-symbols">image</i>
        </span>
    {elseif="!empty($url->images)"}
        <span class="primary icon thumb active color {$url->url|stringToColor}"
            {if="count($url->images) > 1"}
                onclick="Preview_ajaxHttpGallery('{$url->url}', 0)"
            {else}
                onclick="Preview_ajaxHttpShow('{$url->images[0]['url']}')"
            {/if}
            style="background-image: url({$url->images[0]['url']|protectPicture})"
            >
            {if="count($url->images) > 1"}
                <i class="material-symbols">photo_library</i>
            {else}
                <i class="material-symbols">image</i>
            {/if}
        </span>
    {else}
        <span class="primary icon bubble gray">
            {if="$url->provider_icon"}
                <img src="{$url->provider_icon|protectPicture}"/>
            {else}
                <i class="material-symbols">link</i>
            {/if}
        </span>
    {/if}

    {if="$message"}
        <span class="control icon gray active"
            onclick="Chat_ajaxGetMessageContext('{$message->jid}', {$message->mid}); Drawer.clear()">
            <i class="material-symbols">chat_paste_go_2</i>
        </span>

        <span class="control icon gray active divided" onclick="Preview.copyToClipboard('{$url->url}')">
            <i class="material-symbols">content_copy</i>
        </span>
        <span class="control icon gray active" onclick="MovimUtils.openInNew('{$url->url}')">
            <i class="material-symbols">open_in_new</i>
        </span>
    {/if}

    <div>
        {if="$url->messageFile"}
            <p class="line">
                {if="$url->messageFile->isPicture"}
                    <i class="material-symbols">image</i> {$c->__('chats.picture')}
                {elseif="$url->messageFile->isAudio"}
                    <i class="material-symbols">equalizer</i> {$c->__('chats.audio')}
                {elseif="$url->messageFile->isVideo"}
                    <i class="material-symbols">local_movies</i> {$c->__('chats.video')}
                {else}
                    <i class="material-symbols">insert_drive_file</i> {$c->__('avatar.file')}
                {/if}
                <span class="second">•</span>
                <span class="second">{$url->content_length|humanSize}</span>
            </p>
        {elseif="$url->title"}
            <p class="line two" title="{$url->title}">
                {$url->title}
            </p>
        {/if}
        <p class="line two normal" title="{if="!empty($url->description)"}{$url->description}{/if}">
            {if="$url->provider_icon"}
                <span class="icon bubble tiny">
                    <img src="{$url->provider_icon|protectPicture}"/>
                </span>
            {/if}
            {if="$url->provider_name"}
                {$url->provider_name}
            {/if}
            {if="!empty($url->author_name)"}
                <span class="second">•</span>
                <span class="second">{$url->author_name}</span>
            {/if}
            {if="!empty($url->description)"}
                <span class="second">•</span>
                <span class="second">{$url->description}</span>
            {/if}

            {if="$url->messageFile"}
                <span class="second">•</span>
                <span class="second">{$url->url}</span>
            {/if}
        </p>
        {if="$message"}
            <p class="line"><a href="#">{$url->url}</a></p>
        {/if}
    </div>
</li>
