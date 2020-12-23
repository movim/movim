<li class="block">
    {if="!empty($embed->images)"}
        <span class="primary icon thumb active color {$embed->url|stringToColor}"
            {if="count($embed->images) > 1"}
                onclick="Preview_ajaxGallery('{$embed->url}', 0)"
            {else}
                onclick="Preview_ajaxShow('{$embed->images[0]['url']}')"
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

    <div>
        <p class="line">{$embed->title}</p>
        <p class="line">{$embed->description}</p>
    </div>
</li>
