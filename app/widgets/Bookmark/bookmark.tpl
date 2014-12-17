<script type="text/javascript">
    function setBookmark() {
        {$setbookmark}
    }
</script>

<div id="bookmarks">
    {$preparebookmark}
</div>

<div class="padded">
    <a class="button color orange alone"
       href="{$subscriptionconfig}"
       title="{$c->__('bookmarks.configure')}">
        <i class="fa fa-gear"></i> 
    </a>
    
    <!--<a class="button color icon link alone merged" 
       style="float: right;"
       title="{$c->__('url.add')}"
       onclick="movim_toggle_display('#bookmarkurladd')"></a>-->
    <!--<a class="button color blue alone oppose" 
       title="{$c->__('button.refresh')}"
       onclick="{$getbookmark}">
        <i class="fa fa-refresh"></i> 
    </a>-->
</div>
