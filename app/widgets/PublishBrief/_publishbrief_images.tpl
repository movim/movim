<section id="publishbriefimages" class="scroll">
    <ul class="list flex active">
        {loop="$embed->images"}
            <li class="block"
                onclick="PublishBrief.setEmbedImage({$key})">
                <div style="background-image: url('{$value.url}')">
                    <span>
                        {$value.width} × {$value.height} – {$value.size|sizeToCleanSize:0}
                    </span>
                </div>
            </li>
        {/loop}
        <li class="block"
            onclick="PublishBrief.setEmbedImage('none')">
            <div>
                <i class="material-icons">visibility_off</i>
            </div>
        </li>
    </ul>
</section>
<div>
    <button onclick="Drawer.clear();" class="button flat">
        {$c->__('button.close')}
    </button>
</div>
