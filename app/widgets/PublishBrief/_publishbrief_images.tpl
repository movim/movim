<section id="publishbriefimages" class="scroll">
    <ul class="list flex active">
    {loop="$embed->images"}
        <li class="block"
            onclick="PublishBrief.setEmbedImage({$key})">
            <div style="background-image: url('{$value.url}')"></div>
        </li>
    {/loop}
    </ul>
</section>
<div>
    <button onclick="Drawer.clear();" class="button flat">
        {$c->__('button.close')}
    </button>
</div>
