<a
    class="button narrow flat icon gray"
    title="{$c->__('publishbrief.add_snap')}"
    href="#"
    onclick="Snap.init()">
    <i class="material-icons">camera_alt</i>
</a>
<a
    class="button narrow  flat icon gray"
    title="{$c->__('publishbrief.add_link')}"
    href="#"
    onclick="PublishBrief_ajaxLink()">
    <i class="material-icons">link</i>
</a>
{if="$c->getUser()->hasUpload()"}
<a
    class="button narrow flat icon gray"
    href="#"
    title="{$c->__('publishbrief.attach')}"
    onclick="Upload_ajaxRequest()">
    <i class="material-icons">image</i>
</a>
{/if}
