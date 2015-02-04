<form name="post" class="padded_right">
    <input type="hidden" name="to" value="{$to}">
    <input type="hidden" name="node" value="urn:xmpp:microblog:0">
    <div class="icon">
        <input type="text" name="title" placeholder="{$c->__('post.title')}">
        <label for="title">{$c->__('post.title')}</label>
        <span class="icon gray">
            <i class="md md-subject"></i>
        </span>
    </div>
    <div class="icon">
        <textarea name="content" placeholder="{$c->__('post.content')}" onkeyup="movim_textarea_autoheight(this);"></textarea>
        <label for="content">{$c->__('post.content')}</label>
    </div>
    <div class="icon">
        <input
            type="url"
            name="embed"
            placeholder="http://myawesomewebsite.com/"
            onblur="Post_ajaxEmbedTest(this.value)">
        <label for="embed">{$c->__('post.link')}</label>
        <span class="icon gray">
            <i class="md md-link"></i>
        </span>

        <br />
        <article>
            <section>
                <content id="preview"></content>
            </section>
        </article>
        <div id="gallery"></div>
    </div>
</form>
