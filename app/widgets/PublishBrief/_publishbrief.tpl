{if="$extended"}
    <header>
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
                    <i class="material-icons">arrow_back</i>
                </span>

                <p class="line">
                    {if="$post"}
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
    <a id="button_send" class="button action color"
       onclick="PublishBrief.disableSend(); PublishBrief_ajaxHttpDaemonPublish(MovimUtils.formToJson('brief'));">
        <i class="material-icons">send</i>
    </a>
{else}
    <br class="on_desktop"/>
{/if}
<div class="block {if="$extended"}extended{/if}">
    <ul class="list">
        <li>
            {if="!$extended"}
                <span class="control icon active gray"
                    title="{$c->__('publishbrief.post')}"
                    onclick="MovimUtils.reload('{$c->route('publish')}')">
                    <i class="material-icons">add_circle</i>
                </span>
            {else}

            {/if}
            <form onsubmit="return false;" name="brief">
                <input type="hidden" name="to" value="{$to}">
                <input type="hidden" name="node" value="{$node}">
                <input type="hidden" name="id" value="{if="$post"}{$post->nodeid}{/if}">
                <input type="hidden" name="reply" value="{if="$reply"}1{else}0{/if}">
                {if="$reply"}
                    <input type="hidden" name="replyserver" value="{$reply->server}">
                    <input type="hidden" name="replynode" value="{$reply->node}">
                    <input type="hidden" name="replynodeid" value="{$reply->nodeid}">
                {/if}
                <div>
                    <textarea
                        dir="auto"
                        name="title"
                        id="title"
                        rows="1"
                        required
                        data-autoheight="true"
                        placeholder="{$c->__('publishbrief.placeholder')}"
                        type="text">{if="$post"}{$post->title}{elseif="$draft && !empty($draft->title)"}{$draft->title}{elseif="$reply"}{$reply->title}{/if}</textarea>
                </div>
                <div {if="!$extended"}class="hide"{/if}>
                    <textarea
                        {if="$extended"}
                            class="extended"
                        {/if}
                        dir="auto"
                        name="content"
                        placeholder="{$c->__('publishbrief.content_text')}"
                        data-autoheight="true"
                        >{if="$post"}{$post->contentraw}{elseif="$draft && !empty($draft->content)"}{$draft->content}{/if}</textarea>
                </div>
                <input
                    type="checkbox"
                    id="open"
                    name="open"
                    {if="$post && $post->openlink"}
                        checked
                    {/if}
                    style="display: none;"
                >
                <input type="hidden"
                    id="embed"
                    name="embed"
                    onchange="if (this.value != '') { PublishBrief_ajaxEmbedLoading(); PublishBrief_ajaxEmbedTest(this.value, document.querySelector('form[name=brief] input#imagenumber').value); }"
                    {if="$draft && !empty($draft->link)"}
                         value="{$draft->link}"
                    {elseif="$post && $post->attachment"}
                        value="{$post->attachment->href}"
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
            {if="$extended"}
                <span class="control icon active gray"
                    title="{$c->__('publishbrief.preview')}"
                    onclick="PublishBrief_ajaxPreview(MovimUtils.formToJson('brief'))">
                    <i class="material-icons">visibility</i>
                </span>
            {else}
                <span id="button_send"
                    class="control icon gray active"
                    onclick="PublishBrief.disableSend(); PublishBrief_ajaxHttpDaemonPublish(MovimUtils.formToJson('brief'));">
                    <i class="material-icons">send</i>
                </span>
            {/if}
            <span class="control privacy"
                  title="{$c->__('post.public')}">
                <form>
                    <div class="control action">
                        <div class="checkbox">
                            <input
                                id="public"
                                name="public"
                                onchange="PublishBrief.togglePrivacy()"
                                {if="$post && $post->openlink"}
                                    checked
                                {/if}
                                type="checkbox">
                            <label for="public">
                                <i class="material-icons">
                                    {if="$post && $post->openlink"}
                                        wifi_tethering
                                    {else}
                                        lock
                                    {/if}
                                </i>
                            </label>
                        </div>
                    </div>
                </form>
            </span>
            <div>
                <ul class="list embed">
                    {if="$reply"}
                        {autoescape="off"}
                            {$replyblock}
                        {/autoescape}
                    {else}
                        {autoescape="off"}
                            {$embed}
                        {/autoescape}
                    {/if}
                </ul>
            </div>
        </li>
    </ul>
</div>
