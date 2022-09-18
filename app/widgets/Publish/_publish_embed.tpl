{$resolved = $embed->resolve()}

{if="$resolved"}

<li class="block" id="{$embed->HTMLId}">
    <span class="control active icon gray" onclick="Publish_ajaxHttpRemoveEmbed({$embed->draft_id}, {$embed->id})">
        <i class="material-icons">close</i>
    </span>
    {if="count($resolved->images) > 1"}
        {if="$embed->imagenumber > 0"}
            {$imagenumber = $embed->imagenumber-1}
        {/if}
        <span class="primary icon thumb active {if="$embed->imagenumber > 0"}color gray{/if}"
            onclick="Publish_ajaxEmbedChooseImage({$embed->draft_id}, {$embed->id})"

            {if="$embed->imagenumber > 0"}
                style="background-image: url({$resolved->images[$imagenumber]['url']|protectPicture})"
                title="{$resolved->images[$imagenumber]['size']|humanSize}"
            {/if}
            >
            <i class="material-icons">collections</i>
        </span>
    {else}
        {if="!empty($resolved->images) && (count($resolved->images) > 1 || $resolved->images[0]['url'] == $embed->url)"}
            <span class="primary icon thumb active color"
                onclick="Preview_ajaxHttpShow('{$resolved->images[0]['url']}')"
                style="background-image: url({$resolved->images[0]['url']|protectPicture})">
                <i class="material-icons">image</i>
            </span>
        {else}
            <span class="primary icon bubble gray">
                {if="$resolved->providerIcon"}
                    <img src="{$resolved->providerIcon}"/>
                {else}
                    <i class="material-icons">link</i>
                {/if}
            </span>
        {/if}
    {/if}

    <div>
        {if="$resolved->type == 'image'"}
            <p class="line">
                {if="$resolved->images[0]['url'] == $embed->url"}
                    {$c->__('chats.picture')}
                {elseif="!empty($resolved->images) && count($resolved->images) > 1"}
                    {$c->__('chats.picture')}
                    {if="$embed->imagenumber == 0"}
                        -
                    {else}
                        {$embed->imagenumber}
                    {/if}
                    /
                    {$resolved->images|count}
                {/if}
            </p>
            <p class="line">{$resolved->images[$embed->imagenumber]['size']|humanSize}</p>
        {else}
            <p class="line">{$resolved->title}</p>
            <p class="line">{$resolved->description}</p>
        {/if}
    </div>
</li>

{/if}
