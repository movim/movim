{$url = $embed->resolve()}

{if="$url"}
<li class="block" id="{$embed->HTMLId}">
    {if="providerNameIsEmbed($url->provider_name)"}
        <span class="control icon gray" title="{$c->__('publish.embedded_link')}">
            <i class="material-symbols">media_link</i>
        </span>
    {/if}
    <span class="control active icon gray divided" onclick="Publish_ajaxHttpRemoveEmbed({$embed->draft_id}, {$embed->id})">
        <i class="material-symbols">close</i>
    </span>

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
                <img src="{$url->provider_icon}"/>
            {else}
                <i class="material-symbols">link</i>
            {/if}
        </span>
    {/if}

    <div>
        {if="$url->type == 'image'"}
            <p class="line">
                {if="$url->image == $embed->url"}
                    {$c->__('chats.picture')}
                {elseif="!empty($url->images) && count($url->images) > 1"}
                    {$c->__('chats.picture')}
                    {if="$embed->imagenumber == 0"}
                        -
                    {else}
                        {$embed->imagenumber}
                    {/if}
                    /
                    {$url->images|count}
                {/if}
            </p>
            <p class="line">{$url->content_length|humanSize}</p>
        {else}
            <p class="line">{$url->title}</p>
            <p class="line two" title="{if="!empty($url->description)"}{$url->description}{/if}">
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
            </p>
        {/if}
    </div>
</li>

{/if}
