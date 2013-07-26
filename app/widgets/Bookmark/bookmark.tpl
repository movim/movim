<script type="text/javascript">
    function setBookmark() {
        {$setbookmark}
    }
</script>

<h2>{$c->t('Bookmarks')}</h2>

<div id="bookmarks">
    {$preparebookmark}
</div>
<br />
<a class="button color blue icon users alone merged right" 
   style="float: right;"
   title="{$c->t('Add a new Chat Room')}"
   onclick="movim_toggle_display('#bookmarkmucadd')"></a>
<a class="button color icon link alone merged" 
   style="float: right;"
   title="{$c->t('Add a new URL')}"
   onclick="movim_toggle_display('#bookmarkurladd')"></a>
<a class="button black icon alone refresh merged left" 
   style="float: right;"
   title="{$c->t('Refresh')}"
   onclick="{$getbookmark}"></a>
<br />
