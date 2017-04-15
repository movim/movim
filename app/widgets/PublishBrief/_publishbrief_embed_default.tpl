<a class="button flat icon gray" href="#"  onclick="PublishBrief_ajaxLink()">
    <i class="zmdi zmdi-link"></i>
</a>
{if="$c->supported('upload')"}
<a class="button flat icon gray" href="#" onclick="Upload_ajaxRequest()">
    <i class="zmdi zmdi-attachment-alt"></i>
</a>
{/if}
