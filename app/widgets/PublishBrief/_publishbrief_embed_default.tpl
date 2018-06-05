<a
    class="button flat icon gray"
    title="{$c->__('publishbrief.add_link')}"
    href="#"
    onclick="PublishBrief_ajaxLink()">
    <i class="material-icons">link</i>
</a>
{if="$c->getUser()->hasUpload()"}
<a
    class="button flat icon gray"
    href="#"
    title="{$c->__('publishbrief.attach')}"
    onclick="Upload_ajaxRequest()">
    <i class="material-icons">attach_file</i>
</a>
{/if}

