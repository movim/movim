{if="$extended"}
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
                    {if="$item != false"}
                        {$c->__('button.edit')}
                    {elseif="$reply"}
                        {$c->__('button.share')}
                    {else}
                        {$c->__('publishbrief.new')}
                    {/if}
                </p>
            </li>
        </ul>
    </header>
{else}
    <br class="on_desktop"/>
{/if}
<div class="block">
    <ul class="list">
        <li>
            <span id="button_send"
                  class="control icon gray active"
                  onclick="PublishBrief.disableSend(); PublishBrief_ajaxPublish(MovimUtils.formToJson('brief'));">
                <i class="zmdi zmdi-mail-send"></i>
            </span>
            <form onsubmit="return false;" name="brief">
                <input type="hidden" name="to" value="{$to}">
                <input type="hidden" name="node" value="{$node}">
                <input type="hidden" name="id" value="{if="$item != false"}{$item->nodeid}{/if}">
                <input type="hidden" name="reply" value="{if="$reply"}1{else}0{/if}">
                {if="$reply"}
                    <input type="hidden" name="replyorigin" value="{$reply->origin}">
                    <input type="hidden" name="replynode" value="{$reply->node}">
                    <input type="hidden" name="replynodeid" value="{$reply->nodeid}">
                {/if}
                <div>
                    <textarea
                        name="title"
                        id="title"
                        rows="1"
                        required
                        onkeyup="MovimUtils.textareaAutoheight(this);"
                        placeholder="{$c->__('publishbrief.placeholder')}"
                        type="text">{if="$item != false"}{$item->title}{elseif="!empty($draft->title)"}{$draft->title}{elseif="$reply"}{$reply->title}{/if}</textarea>
                </div>
                <div {if="!$extended"}class="hide"{/if}>
                    <textarea
                        name="content"
                        placeholder="{$c->__('publishbrief.content_text')}"
                        oninput="MovimUtils.textareaAutoheight(this);"
                        >{if="$item != false"}{$item->contentraw}{elseif="!empty($draft->content)"}{$draft->content}{/if}</textarea>
                </div>
                <input
                    type="checkbox"
                    id="open"
                    name="open"
                    checked
                    style="display: none;"
                >
                {if="$item != false"}
                    {$attachment = $item->getAttachment()}
                {/if}
                <input type="hidden"
                    id="embed"
                    name="embed"
                    onchange="if(this.value != '') { PublishBrief_ajaxEmbedLoading(); PublishBrief_ajaxEmbedTest(this.value, document.querySelector('form[name=brief] input#imagenumber').value); }"
                    {if="!empty($draft->links) && !empty($draft->links[0])"}
                         value="{$draft->links[0]}"
                    {elseif="isset($attachment) && $attachment != false"}
                        value="{$attachment.href}"
                    {elseif="$url"}
                        value="{$url}"
                    {/if}
                >
                <input type="hidden"
                    id="imagenumber"
                    name="imagenumber"
                    value="0">
            </form>
        </li>
    </ul>

    <ul class="list middle">
        <li>
            <span class="primary icon gray bubble active privacy"
                  title="{$c->__('post.public')}"
                  onclick="PublishBrief.togglePrivacy()">
                <i class="zmdi zmdi-portable-wifi"></i>
            </span>
            {if="$extended"}
                <span class="control icon active gray"
                    title="{$c->__('publishbrief.preview')}"
                    onclick="PublishBrief_ajaxPreview(MovimUtils.formToJson('brief'))">
                    <i class="zmdi zmdi-eye"></i>
                </span>
            {else}
                <span class="control icon active gray"
                    title="{$c->__('publishbrief.post')}"
                    onclick="MovimUtils.reload('{$c->route('publish')}')">
                    <i class="zmdi zmdi-plus-circle"></i>
                </span>
            {/if}
            <div>
                <ul class="normal list embed flex">
                    {if="$reply"}
                        {$replyblock}
                    {else}
                        {$embed}
                    {/if}
                </ul>
            </div>
        </li>
    </ul>
</div>
