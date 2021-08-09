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
                <img src="{$embed->providerIcon}"/>
            {else}
                <i class="material-icons">link</i>
            {/if}
        </span>
    {/if}

    {if="$withlink"}
        <span class="control icon gray" onclick="MovimUtils.openInNew('{$embed->url}')">
            <i class="material-icons">open_in_new</i>
        </span>
    {/if}

    <div  {if="$withlink"}onclick="MovimUtils.openInNew('{$embed->url}')"{/if}>
        <p class="line">{$embed->title}</p>
        <p class="line">{$embed->description}</p>
        {if="$withlink"}
            <p class="line"><a href="#">{$embed->url}</a></p>
        {/if}
    </div>
</li>
