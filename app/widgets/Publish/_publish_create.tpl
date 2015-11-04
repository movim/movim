<form name="post" class="block">
    <input type="hidden" name="to" value="{$to}">
    <input type="hidden" name="node" value="{$node}">
    <input type="hidden" name="id" value="{if="$item != false"}{$item->nodeid}{/if}">

    <div>
        <input type="text" name="title" placeholder="{$c->__('post.title')}" {if="$item != false"}value="{$item->title}"{/if}>
        <label for="title">{$c->__('post.title')}</label>
    </div>

    <div id="content_link">
        {if="$item != false"}
            {$attachement = $item->getAttachement()}
        {/if}
        <input
            type="url"
            name="embed"
            placeholder="http://myawesomewebsite.com/ or http://mynicepictureurl.com/"
            onpaste="var e=this; setTimeout(function(){Publish_ajaxEmbedTest(e.value);}, 4);"
            {if="$attachement != false"}value="{$attachement.href}"{/if}
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
        <textarea name="content" placeholder="{$c->__('post.content_text')}" oninput="movim_textarea_autoheight(this);">{if="$item != false"}{$item->contentraw}{/if}</textarea>
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
    </ul>
</form>
