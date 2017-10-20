<header class="relative">
    <ul class="list middle">
        <li>
            <span
                class="primary icon active"
                {if="$node == 'urn:xmpp:microblog:0'"}
                    onclick="MovimUtils.redirect('{$c->route('news')}');"
                {else}
                    onclick="history.back();"
                {/if}
            >
                <i class="zmdi zmdi-arrow-back"></i>
            </span>

            <p class="line">
                {if="$reply"}
                    {$c->__('button.share')}
                {elseif="$item != false"}
                    {$c->__('publish.edit')}
                {else}
                    {$c->__('publish.new')}
                {/if}
            </p>
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

    <span
        title="{$c->__('menu.add_post')}"
        id="button_send"
        class="button action color"
        onclick="Publish.disableSend(); Publish_ajaxPublish(MovimUtils.formToJson('post'));">
        <i class="zmdi zmdi-mail-send"></i>
    </span>
</header>

<form name="post" class="block padded">
    {if="$reply"}
    <ul class="list thick card">
        <li></li>
        <li class="block">
            {if="$reply->picture"}
                <span
                    class="primary icon thumb"
                    style="background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7) 0%, rgba(0, 0, 0, 0.3) 100%), url({$reply->picture});"></span>
            {/if}
            <p class="line">{$reply->title}</p>
            <p>{$reply->getSummary()}</p>
            <p>
                {if="$reply->isMicroblog()"}
                    <i class="zmdi zmdi-account"></i> {$reply->getContact()->getTrueName()}
                {else}
                    <i class="zmdi zmdi-pages"></i> {$reply->node}
                {/if}
                <span class="info">
                    {$reply->published|strtotime|prepareDate:true,true}
                </span>
            </p>
        </li>
    </ul>
    {/if}

    <input type="hidden" name="to" value="{$to}">
    <input type="hidden" name="node" value="{$node}">
    <input type="hidden" name="reply" value="{if="$reply"}1{else}0{/if}">
    {if="$reply"}
        <input type="hidden" name="replyorigin" value="{$reply->origin}">
        <input type="hidden" name="replynode" value="{$reply->node}">
        <input type="hidden" name="replynodeid" value="{$reply->nodeid}">
    {/if}
    <input type="hidden" name="id" value="{if="$item != false"}{$item->nodeid}{/if}">

    <div>
        <textarea
            name="title"
            placeholder="{$c->__('post.title')}"
            style="height: 68px"
            oninput="MovimUtils.textareaAutoheight(this);">{if="!empty($draft->title)"}{$draft->title}{elseif="$item != false"}{$item->title}{elseif="$reply"}{$reply->title}{/if}</textarea>
        <label for="title">{$c->__('post.title')}</label>
    </div>

    <div id="content_link" {if="$reply"}class="hide"{/if}>
        {if="$item != false"}
            {$attachment = $item->getAttachment()}
        {/if}
        <button
            class="button oppose flat gray"
            type="button"
            style="margin-top: 4rem"
            onclick="Publish_ajaxClearShareUrl()">
            <i class="zmdi zmdi-close"></i>
        </button>
        <input
            type="url"
            style="width: calc(100% - 6rem)"
            name="embed"
            placeholder="http://myawesomewebsite.com/ or http://mynicepictureurl.com/"
            onpaste="var e=this; setTimeout(function(){Publish_ajaxEmbedTest(e.value);}, 4);"
            {if="!empty($draft->links) && !empty($draft->links[0])"}
                value="{$draft->links[0]}"
            {elseif="isset($attachment) && $attachment != false"}
                value="{$attachment.href}"
            {elseif="$url"}
                value="{$url}"
            {/if}
        >
        <label for="embed">{$c->__('publish.link')}</label>

        <ul class="list middle" id="preview"></ul>
        <div id="gallery"></div>
    </div>

    <div id="enable_content" onclick="Publish.enableContent();"
        {if="!empty($draft->content)"}
            class="hide"
        {else}
            class="show"
        {/if}
        >
        <input type="text" value="{$c->__('publish.add_text')}"/>
        <label>{$c->__('publish.add_text_label')}</label>
    </div>
    <div id="content_field"
        {if="empty($draft->content)"}
            class="hide"
        {else}
            class="show"
        {/if}
        >
        <textarea name="content" placeholder="{$c->__('publish.content_text')}" oninput="MovimUtils.textareaAutoheight(this);">{if="!empty($draft->content)"}{$draft->content}{elseif="$item != false"}{$item->contentraw}{/if}</textarea>
        <label for="content">{$c->__('publish.content_label')}</label>

        <button class="button oppose flat gray" type="button" onclick="Publish_ajaxHelp()">
            <i class="zmdi zmdi-help"></i> {$c->__('publish.help')}
        </button>
        <button class="button oppose flat gray" type="button" onclick="Publish_ajaxPreview(MovimUtils.formToJson('post'))">
            <i class="zmdi zmdi-eye"></i> {$c->__('publish.preview')}
        </button>
    </div>

    <hr class="clear" />

    <ul class="list middle active {if="$reply"}hide{/if}">
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
        <ul class="list middle">
            <li>
                <span class="primary">
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

