<li class="block">
    {if="!empty($embed->images)"}
        <span class="primary icon thumb active color {$embed->url|stringToColor}"
            {if="count($embed->images) > 1"}
                onclick="Preview_ajaxHttpGallery('{$embed->url}', 0)"
            {else}
                onclick="Preview_ajaxHttpShow('{$embed->images[0]['url']}')"
            {/if}
            style="background-image: url({$embed->images[0]['url']|protectPicture})"
            >
            {if="count($embed->images) > 1"}
                <i class="material-symbols">photo_library</i>
            {else}
                <i class="material-symbols">image</i>
            {/if}
        </span>
    {else}
        <span class="primary icon bubble gray">
            {if="$embed->providerIcon"}
                <img src="{$embed->providerIcon|protectPicture}"/>
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

        <span class="control icon gray active divided" onclick="Preview.copyToClipboard('{$embed->url}')">
            <i class="material-symbols">content_copy</i>
        </span>
        <span class="control icon gray active" onclick="MovimUtils.openInNew('{$embed->url}')">
            <i class="material-symbols">open_in_new</i>
        </span>
    {/if}

    <div>
        <p class="line two" title="{$embed->title}">{$embed->title}</p>
        <p class="line two" title="{if="!empty($embed->description)"}{$embed->description}{/if}">
            {if="$embed->providerIcon"}
                <span class="icon bubble tiny">
                    <img src="{$embed->providerIcon|protectPicture}"/>
                </span>
            {/if}
            {if="$embed->providerName"}
                {$embed->providerName}
            {/if}
            {if="!empty($embed->authorName)"}
                <span class="second">•</span>
                <span class="second">{$embed->authorName}</span>
            {/if}
            {if="!empty($embed->description)"}
                <span class="second">•</span>
                <span class="second">{$embed->description}</span>
            {/if}
        </p>
        {if="$message"}
            <p class="line"><a href="#">{$embed->url}</a></p>
        {/if}
    </div>
</li>
