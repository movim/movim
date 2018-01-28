<section id="publishbriefimages" class="scroll">
    <ul class="list flex active">
    {loop="$embed->images"}
        {if="$key == 0 || ($value.width > 300 && $value.height > 300)"}
        <li class="block"
            onclick="PublishBrief.setEmbedImage({$key})">
            <div style="background-image: url('{$value.url}')"></div>
        </li>
        {/if}
    {/loop}
    </ul>
</section>
<div>
    <button onclick="Drawer.clear();" class="button flat">
        {$c->__('button.close')}
    </button>
</div>
