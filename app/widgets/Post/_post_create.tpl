<form name="post" class="padded_top_bottom">
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
        <input
            type="url"
            name="embed"
            placeholder="http://myawesomewebsite.com/ or http://mynicepictureurl.com/"
            onPaste="var e=this; setTimeout(function(){Post_ajaxEmbedTest(e.value);}, 4);"
        >
        <label for="embed">{$c->__('post.link')}</label>
        <ul class="simple">
            <li>
                <p><i class="md md-image"></i> {$c->__('post.embed_tip')}</p>
            </li>
        </ul>

        <article>
            <section>
                <content id="preview"></content>
            </section>
        </article>
        <div id="gallery"></div>
    </div>
</form>
