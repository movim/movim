<a
    class="button flat icon gray"
    title="{$c->__('publish.link')}"
    href="#"
    onclick="PublishBrief_ajaxLink()">
    <i class="zmdi zmdi-link"></i>
</a>
{if="$c->supported('upload')"}
<a
    class="button flat icon gray"
    href="#"
    title="{$c->__('publish.attach')}"
    onclick="Upload_ajaxRequest()">
    <i class="zmdi zmdi-attachment-alt"></i>
</a>
{/if}

