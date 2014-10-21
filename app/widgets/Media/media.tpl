<script type="text/javascript">
    function refreshMedia() {
        {$refresh}
    }
</script>

{if="isset($_GET['f'])"}
    <div class="tabelem" title="{$c->__('page.viewer')}" id="viewer">
        {$c->pictureViewer($_GET['f'])}
    </div>
{/if}
<div class="tabelem" title="{$c->__('page.media')}" id="media">    
    {$c->mainFolder();}
    <div class="clear"></div>
</div>
