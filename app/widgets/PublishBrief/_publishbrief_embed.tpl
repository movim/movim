<li class="block large">
    <span class="control active icon gray" onclick="PublishBrief.clearEmbed()">
        <i class="material-icons">close</i>
    </span>
    {if="!empty($embed->images)"}
        <span class="primary icon thumb active color gray"
            {if="count($embed->images) > 1"}
                onclick="PublishBrief_ajaxEmbedChooseImage('{$embed->url}')"
            {/if}
            style="background-image: url({$embed->images[$imagenumber]['url']})"
            title="{$embed->images[$imagenumber]['width']} x {$embed->images[$imagenumber]['height']} - {$embed->images[$imagenumber]['size']|sizeToCleanSize}">
            {if="count($embed->images) > 1"}
                <i class="material-icons">collections</i>
            {/if}
        </span>
    {else}
        <span class="primary icon bubble gray">
        {if="$embed->type == 'photo'"}
            <i class="material-icons">image</i>
        {else}
            {if="$embed->providerIcon"}
                <img src="{$embed->providerIcon}"/>
            {else}
                <i class="material-icons">link</i>
            {/if}
        {/if}
        </span>
    {/if}

    {if="$embed->type == 'photo'"}
        <p class="line">{$embed->images[$imagenumber]['width']} x {$embed->images[$imagenumber]['height']}</p>
        <p class="line">{$embed->images[$imagenumber]['size']|sizeToCleanSize}</p>
    {else}
        <p class="line">{$embed->title}</p>
        <p class="line">{$embed->description}</p>
    {/if}
    <p class="line">
        <a href="{$embed->url}" target="_blank">
            {$embed->url}
        </a>
    </p>
</li>
