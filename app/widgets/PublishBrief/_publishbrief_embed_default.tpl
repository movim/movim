<a
    class="button flat icon gray"
    title="{$c->__('publishbrief.add_link')}"
    href="#"
    onclick="PublishBrief_ajaxLink()">
    <i class="zmdi zmdi-link"></i>
</a>
{if="$c->supported('upload')"}
<a
    class="button flat icon gray"
    href="#"
    title="{$c->__('publishbrief.attach')}"
    onclick="Upload_ajaxRequest()">
    <i class="zmdi zmdi-attachment-alt"></i>
</a>
{/if}

