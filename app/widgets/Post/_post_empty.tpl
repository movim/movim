<!--<div class="placeholder icon newspaper">
    <h1>{$c->__('post.news_feed')}</h1>
    <h4>{$c->__('post.placeholder')}</h4>
</div>-->
<ul class="simple">
    <li>
        <!--
        <form>
            <div class="control action">
                <span style="display: inline; float: left; margin-right: 2rem;"><i class="md md-insert-comment"></i></span>
                <div class="checkbox" style="float: left;">
                    <input
                        type="checkbox"
                        id="privacy"
                        name="privacy"
                        {if="$me->privacy"}
                            checked
                        {/if}
                        onchange="{$privacy}">
                    <label for="privacy"></label>
                </div>
                <span style="display: inline; float: right; margin-left: 2rem;"><i class="md md-mode-edit"></i></span>
            </div>
        </form>-->
        <form name="post">
            <div>
                <input type="text" name="title" placeholder="Optionnel">
                <label for="title">Title</label>
            </div>
            <div>
                <textarea name="content" placeholder="Content" onkeyup="movim_textarea_autoheight(this);"></textarea>
                <label for="content">Content</label>
            </div>
            <div>
                <input type="url" name="embed" placeholder="http://myawesomewebsite.com/" onblur="Post_ajaxEmbedTest(this.value)">
                <label for="embed">Link</label>

                <div id="preview">

                </div>
            </div>
            <div>
                <input type="text" name="tags" placeholder="food, blog, news">
                <label for="tags">Tags</label>
            </div>
        </form>
    </li>
</ul>
