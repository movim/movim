{$resolved = $embed->resolve()}

{if="$resolved"}
<li class="block" id="{$embed->HTMLId}">
    {if="providerNameIsEmbed($resolved->providerName)"}
        <span class="control icon gray" title="{$c->__('publish.embedded_link')}">
            <i class="material-symbols">media_link</i>
        </span>
    {/if}
    <span class="control active icon gray divided" onclick="Publish_ajaxHttpRemoveEmbed({$embed->draft_id}, {$embed->id})">
        <i class="material-symbols">close</i>
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
            <i class="material-symbols">collections</i>
        </span>
    {else}
        {if="!empty($resolved->images)"}
            <span class="primary icon thumb active color {$resolved->url|stringToColor}"
                {if="count($resolved->images) > 1"}
                    onclick="Preview_ajaxHttpGallery('{$resolved->url}', 0)"
                {else}
                    onclick="Preview_ajaxHttpShow('{$resolved->images[0]['url']}')"
                {/if}
                style="background-image: url({$resolved->images[0]['url']|protectPicture})"
                >
                {if="count($resolved->images) > 1"}
                    <i class="material-symbols">photo_library</i>
                {else}
                    <i class="material-symbols">image</i>
                {/if}
            </span>
        {else}
            <span class="primary icon bubble gray">
                {if="$resolved->providerIcon"}
                    <img src="{$resolved->providerIcon}"/>
                {else}
                    <i class="material-symbols">link</i>
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
            <p class="line two" title="{if="!empty($resolved->description)"}{$resolved->description}{/if}">
                {if="$resolved->providerIcon"}
                    <span class="icon bubble tiny">
                        <img src="{$resolved->providerIcon|protectPicture}"/>
                    </span>
                {/if}
                {if="$resolved->providerName"}
                    {$resolved->providerName}
                {/if}
                {if="!empty($resolved->authorName)"}
                    <span class="second">•</span>
                    <span class="second">{$resolved->authorName}</span>
                {/if}
                {if="!empty($resolved->description)"}
                    <span class="second">•</span>
                    <span class="second">{$resolved->description}</span>
                {/if}
            </p>
        {/if}
    </div>
</li>

{/if}
