<!--<div class="placeholder icon newspaper">
    <h1>{$c->__('post.news_feed')}</h1>
    <h4>{$c->__('post.placeholder')}</h4>
</div>-->
<ul class="simple">
    <li class="action">
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
        </form>
        <h2>New post</h2>    
    </li>
</ul>
<form>
    <div>
        <input type="text" placeholder="Optionnel">
        <label>Title</label>
    </div>
    <div>
        <textarea placeholder="Content"></textarea>
        <label>Content</label>
    </div>
    <div>
        <input type="url" placeholder="http://myawesomewebsite.com/" onblur="Post_ajaxEmbedTest(this.value)">
        <label>Link</label>

        <div id="preview">

        </div>
    </div>
    <div>
        <input type="text" placeholder="food, blog, news">
        <label>Tags</label>
    </div>
</form>

