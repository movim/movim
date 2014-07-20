<script type="text/javascript">
    function setBookmark() {
        {$setbookmark}
    }
</script>

<!--<h2>{$c->t('Bookmarks')}</h2>-->

<div id="bookmarks" class="paddedtop">
    {$preparebookmark}
</div>

<div class="padded">
    <a class="button black alone"
       href="{$subscriptionconfig}"
       title="{$c->t('Configure')}">
        <i class="fa fa-gear"></i> 
    </a>
    <a class="button color blue alone merged right oppose" 
       title="{$c->t('Add a new Chat Room')}"
       onclick="movim_toggle_display('#bookmarkmucadd')">
        <i class="fa fa-comments"></i> 
    </a>
    <!--<a class="button color icon link alone merged" 
       style="float: right;"
       title="{$c->t('Add a new URL')}"
       onclick="movim_toggle_display('#bookmarkurladd')"></a>-->
    <a class="button black alone merged left oppose" 
       title="{$c->t('Refresh')}"
       onclick="{$getbookmark}">
        <i class="fa fa-refresh"></i> 
    </a>
</div>
