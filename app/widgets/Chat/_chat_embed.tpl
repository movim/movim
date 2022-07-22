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
                <i class="material-icons">photo_library</i>
            {else}
                <i class="material-icons">image</i>
            {/if}
        </span>
    {else}
        <span class="primary icon bubble gray">
            {if="$embed->providerIcon"}
                <img src="{$embed->providerIcon|protectPicture}"/>
            {else}
                <i class="material-icons">link</i>
            {/if}
        </span>
    {/if}

    {if="$withlink"}
        <span class="control icon gray active" onclick="Preview.copyToClipboard('{$embed->url}')">
            <i class="material-icons">content_copy</i>
        </span>
        <span class="control icon gray active" onclick="MovimUtils.openInNew('{$embed->url}')">
            <i class="material-icons">open_in_new</i>
        </span>
    {/if}

    <div>
        <p class="line two" title="{$embed->title}">{$embed->title}</p>
        {if="!empty($embed->description)"}
            <p class="line" title="{$embed->description}">{$embed->description}</p>
        {else}
            <p class="line"></p>
        {/if}
        {if="$withlink"}
            <p class="line"><a href="#">{$embed->url}</a></p>
        {/if}
    </div>
</li>
