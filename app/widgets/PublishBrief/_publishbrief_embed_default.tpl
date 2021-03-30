<a
    class="button narrow flat icon gray"
    title="{$c->__('publish.add_link')}"
    href="#"
    onclick="PublishBrief_ajaxLink()">
    <i class="material-icons">link</i>
</a>
{if="$c->getUser()->hasUpload()"}
<a
    class="button narrow flat icon gray"
    title="{$c->__('publish.add_snap')}"
    href="#"
    onclick="Snap.init()">
    <i class="material-icons">camera_alt</i>
</a>
<a
    class="button narrow flat icon gray"
    title="{$c->__('draw.title')}"
    href="#"
    onclick="Draw.init()">
    <i class="material-icons">gesture</i>
</a>
<a
    class="button narrow flat icon gray"
    href="#"
    title="{$c->__('publish.attach')}"
    onclick="Upload_ajaxRequest()">
    <i class="material-icons">image</i>
</a>
{/if}
