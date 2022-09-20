{$resolved = $embed->resolve()}

<section id="publishimages" class="scroll">
    <ul class="grid third active">
        {loop="$resolved->images"}
            <li style="background-image: url('{$value.url|protectPicture}')"
                onclick="Publish_ajaxHttpSetImageNumber({$embed->draft_id}, {$embed->id}, {$key+1})">
                <i class="material-icons">photo</i>
                <span>
                    {$value.width} × {$value.height} · {$value.size|humanSize:0}
                </span>
            </li>
        {/loop}
        <li onclick="Publish_ajaxHttpSetImageNumber({$embed->draft_id}, {$embed->id}, 0)">
            <i class="material-icons">visibility_off</i>
        </li>
    </ul>
</section>
<div>
    <button onclick="Drawer.clear();" class="button flat">
        {$c->__('button.close')}
    </button>
</div>
