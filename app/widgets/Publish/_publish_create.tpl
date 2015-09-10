<form name="post" class="block">
    <input type="hidden" name="to" value="{$to}">
    <input type="hidden" name="node" value="{$node}">
    <div>
        <input type="text" name="title" placeholder="{$c->__('post.title')}">
        <label for="title">{$c->__('post.title')}</label>
    </div>

    <div>
        <input
            type="url"
            name="embed"
            placeholder="http://myawesomewebsite.com/ or http://mynicepictureurl.com/"
            onPaste="var e=this; setTimeout(function(){Publish_ajaxEmbedTest(e.value);}, 4);"
        >
        <label for="embed">{$c->__('post.link')}</label>

        <article>
            <section>
                <content id="preview"></content>
            </section>
        </article>
        <div id="gallery"></div>
    </div>
    
    <div id="enable_content" onclick="Publish.enableContent();">
        <input type="text" value="{$c->__('publish.add_text')}"/>
        <label>{$c->__('publish.add_text_label')}</label>
    </div>
    <div id="content_field">
        <textarea name="content" placeholder="{$c->__('post.content_text')}" onkeyup="movim_textarea_autoheight(this);"></textarea>
        <label for="content">{$c->__('post.content_label')}</label>
    </div>

    <ul class="middle flex active">
        {if="$c->supported('upload')"}
        <li class="block large" onclick="Upload_ajaxRequest()">
            <span class="icon">
                <i class="zmdi zmdi-attachment-alt"></i>
            </span>
            <span>{$c->__('publish.attach')}</span>
        </li>
        {/if}
        <li class="subheader">{$c->__('post.embed_tip')}</li>
        <a class="block" target="_blank" href="http://imgur.com/">
            <li class="block action">
                <div class="action">
                    <i class="zmdi zmdi-chevron-right"></i>
                </div>
                <span class="bubble icon">
                    <img src="https://userecho.com/s/logos/2015/2015.png">
                </span>
                Imgur
            </li>
        </a>
        <a class="block" target="_blank" href="https://www.flickr.com/">
            <li class="action">
                <div class="action">
                    <i class="zmdi zmdi-chevron-right"></i>
                </div>
                <span class="bubble icon">
                    <img src="https://www.flickr.com/apple-touch-icon.png">
                </span>
                Flickr
            </li>
        </a>
    </ul>
</form>
