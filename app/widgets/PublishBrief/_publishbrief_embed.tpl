<li class="block">
    <span class="control active icon gray" onclick="PublishBrief.clearEmbed()">
        <i class="zmdi zmdi-close"></i>
    </span>
    <span class="primary icon bubble gray">

    {if="$embed->type == 'photo'"}
        <i class="zmdi zmdi-image"></i>
    {else}
        {if="$embed->providerIcon"}
            <img src="{$embed->providerIcon}"/>
        {else}
            <i class="zmdi zmdi-link"></i>
        {/if}
    {/if}
    </span>
    {if="isset($embed->images)"}
        <span class="control icon"
            title="{$embed->images[0]['width']} x {$embed->images[0]['height']} - {$embed->images[0]['size']|sizeToCleanSize}">
            <img src="{$embed->images[0]['url']}"></img>
        </span>
    {/if}
    {if="$embed->type == 'photo'"}
        <p class="line">{$embed->images[0]['width']} x {$embed->images[0]['height']}</p>
        <p class="line">{$embed->images[0]['size']|sizeToCleanSize}</p>
    {else}
        <p class="line">{$embed->title}</p>
        <p class="line">{$embed->description}</p>
    {/if}
</li>
