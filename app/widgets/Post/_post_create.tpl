<ul class="simple">
    <li>
        <form name="post">
            <input type="hidden" name="to" value="{$to}">
            <input type="hidden" name="node" value="urn:xmpp:microblog:0">
            <div>
                <input type="text" name="title" placeholder="{$c->__('post.title')}">
                <label for="title">{$c->__('post.title')}</label>
            </div>
            <div>
                <textarea name="content" placeholder="{$c->__('post.content')}" onkeyup="movim_textarea_autoheight(this);"></textarea>
                <label for="content">{$c->__('post.content')}</label>
            </div>
            <div>
                <input type="url" name="embed" placeholder="http://myawesomewebsite.com/" onblur="Post_ajaxEmbedTest(this.value)">
                <label for="embed">{$c->__('post.link')}</label>
            </div>
            <!--
            <div>
                <input type="text" name="tags" placeholder="food, blog, news">
                <label for="tags">{$c->__('post.tags')}</label>
            </div>
            -->
        </form>
    </li>
    <article>
        <section>
            <content id="preview">


            </content>
        </section>
    </article>
</ul>
