{if="$embed->images != null"}
<li class="block">
    <span class="primary icon bubble" style="background-image: url({$embed->images[0]['url']|htmlspecialchars});"></span>
    <span class="control active icon gray" onclick="PublishBrief.clearEmbed()">
        <i class="zmdi zmdi-close"></i>
    </span>
    <p>
        {$embed->images[0]['width']} x {$embed->images[0]['height']}
    </p>
    <p>
        {$embed->images[0]['size']|sizeToCleanSize}
    </p>
</li>
{/if}
