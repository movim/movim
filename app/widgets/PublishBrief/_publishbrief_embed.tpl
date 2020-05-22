<li class="block large">
    <span class="control active icon gray" onclick="PublishBrief.clearEmbed()">
        <i class="material-icons">close</i>
    </span>
    {if="!empty($embed->images)"}
        <span class="primary icon thumb active color gray"
            onclick="PublishBrief_ajaxEmbedChooseImage('{$embed->url}')"
            style="background-image: url({$embed->images[$imagenumber]['url']|protectPicture})"
            {if="$imagenumber != 'none'"}
                title="{$embed->images[$imagenumber]['width']} x {$embed->images[$imagenumber]['height']} - {$embed->images[$imagenumber]['size']|sizeToCleanSize}"
            {/if}
            >
            <i class="material-icons">collections</i>
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

    <div>
        {if="$embed->type == 'photo'"}
            <p class="line">{$embed->images[$imagenumber]['width']} x {$embed->images[$imagenumber]['height']}</p>
            <p class="line">{$embed->images[$imagenumber]['size']|sizeToCleanSize}</p>
        {else}
            <p class="line">{$embed->title}</p>
            <p class="line">{$embed->description}</p>
        {/if}

        <p class="line">
            {if="!empty($embed->images)"}
                {$c->__('chats.picture')}
                {if="$imagenumber != 'none'"}{$imagenumber+1}{else}1{/if}/{$embed->images|count}
                ·
            {/if}
            <a href="{$embed->url}" target="_blank">
                {$embed->url}
            </a>
        </p>
    </div>
</li>
