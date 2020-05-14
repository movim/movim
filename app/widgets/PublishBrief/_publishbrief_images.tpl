<section id="publishbriefimages" class="scroll">
    <ul class="grid third active">
        {loop="$embed->images"}
            <li style="background-image: url('{$value.url|protectPicture}')"
                onclick="PublishBrief.setEmbedImage({$key})">
                <i class="material-icons">photo</i>
                <span>
                    {$value.width} × {$value.height} · {$value.size|sizeToCleanSize:0}
                </span>
            </li>
        {/loop}
        <li onclick="PublishBrief.setEmbedImage('none')">
            <i class="material-icons">visibility_off</i>
        </li>
    </ul>
</section>
<div>
    <button onclick="Drawer.clear();" class="button flat">
        {$c->__('button.close')}
    </button>
</div>
