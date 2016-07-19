<header>
    <ul class="list middle">
        <li>
            <span class="primary icon active" onclick="Publish.headerBack('{$to}', '{$node}', false);">
                <i class="zmdi zmdi-arrow-back"></i>
            </span>

            <span id="button_send" class="control icon active" onclick="Publish.disableSend(); Publish_ajaxPublish(MovimUtils.formToJson('post'));">
                <i class="zmdi zmdi-mail-send"></i>
            </span>
            <span class="control icon active" onclick="Publish_ajaxHelp()">
                <i class="zmdi zmdi-help"></i>
            </span>
            <span class="control icon active" onclick="Publish_ajaxPreview(MovimUtils.formToJson('post'))">
                <i class="zmdi zmdi-eye"></i>
            </span>

            {if="$item != false"}
                <p class="line">{$c->__('publish.edit')}</p>
            {else}
                <p class="line">{$c->__('publish.new')}</p>
            {/if}
            <!--
            <p>
                {if="$item != null && $item->node != 'urn:xmpp:microblog:0'"}
                    {if="$item->name"}
                        {$item->name}
                    {else}
                        {$item->node}
                    {/if}
                {else}
                    {$c->__('page.blog')}
                {/if}
            </p>-->
        </li>
    </ul>
</header>

<form name="post" class="block padded">
    <input type="hidden" name="to" value="{$to}">
    <input type="hidden" name="node" value="{$node}">
    <input type="hidden" name="id" value="{if="$item != false"}{$item->nodeid}{/if}">

    <div>
        <input type="text" name="title" placeholder="{$c->__('post.title')}" {if="$item != false"}value="{$item->title|htmlspecialchars}"{/if}>
        <label for="title">{$c->__('post.title')}</label>
    </div>

    <div id="content_link">
        {if="$item != false"}
            {$attachment = $item->getAttachment(true)}
        {/if}
        <input
            type="url"
            name="embed"
            placeholder="http://myawesomewebsite.com/ or http://mynicepictureurl.com/"
            onpaste="var e=this; setTimeout(function(){Publish_ajaxEmbedTest(e.value);}, 4);"
            {if="isset($attachment) && $attachment != false"}value="{$attachment.href}"{/if}
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
    <div id="content_field" class="hide">
        <textarea name="content" placeholder="{$c->__('post.content_text')}" oninput="MovimUtils.textareaAutoheight(this);">{if="$item != false"}{$item->contentraw}{/if}</textarea>
        <label for="content">{$c->__('post.content_label')}</label>
    </div>

    <div>
        {if="$item != false"}
            {$tags = $item->getTagsImploded()}
        {/if}
        <input
            type="text"
            name="tags"
            placeholder="write, comma separated, tags"
            {if="isset($tags)"}
                value="{$tags}"
            {/if}>
        <label for="title">{$c->__('post.tags')}</label>
    </div>

    <ul class="list middle active">
        {if="$c->supported('upload')"}
        <li class="block large" onclick="Upload_ajaxRequest()">
            <span class="primary icon gray">
                <i class="zmdi zmdi-attachment-alt"></i>
            </span>
            <p class="normal line">{$c->__('publish.attach')}</p>
        </li>
        {/if}
    </ul>

    <div>
        <ul class="list thin">
            <li>
                <span class="primary icon gray">
                    <i class="zmdi zmdi-portable-wifi"></i>
                </span>
                <span class="control">
                    <div class="action">
                        <div class="checkbox">
                            <input
                                type="checkbox"
                                id="open"
                                name="open"
                                {if="$item != false && $item->open"}
                                    checked
                                {/if}
                            >
                            <label for="open"></label>
                        </div>
                    </div>
                </span>
                <p class="line normal">
                    {$c->__('post.public')}
                </p>
            </li>
        </ul>
    </div>
</form>
