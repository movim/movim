
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
            {elseif="$url->content_type"}
                <i class="material-symbols">{$url->content_type|mimeToIcon}</i>
            {else}
                <i class="material-symbols">link</i>
            {/if}
        </span>
    {/if}

    <div>
        {if="$url->type == 'image'"}
            <p class="line">
                {$c->__('chats.picture')}
            </p>
            <p class="line">{$url->content_length|humanSize}</p>
        {else}
            <p class="line">{if="$url->title"}{$url->title}{else}{$url->content_type|mimeToLabel}{/if}</p>
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
                {if="empty($url->title)"}
                    <span class="second">{$url->content_length|humanSize}</span>
                    <br />
                    <a href="{$url->url}" target="_blank">{$url->url}</a>
                {/if}
            </p>
        {/if}
    </div>
</li>