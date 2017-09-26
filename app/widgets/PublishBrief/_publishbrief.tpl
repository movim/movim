<br class="on_desktop"/>
<div class="block">
    <ul class="list">
        <li>
            <span id="button_send"
                  class="control icon gray active"
                  onclick="PublishBrief.disableSend(); PublishBrief_ajaxPublish(MovimUtils.formToJson('brief'));">
                <i class="zmdi zmdi-mail-send"></i>
            </span>
            <form onsubmit="return false;" name="brief">
                <div>
                    <textarea
                        name="title"
                        id="title"
                        rows="1"
                        required

                        onkeyup="MovimUtils.textareaAutoheight(this);"
                        placeholder="{$c->__('publishbrief.placeholder')}"
                        type="text">{if="!empty($draft->title)"}{$draft->title}{/if}</textarea>
                </div>
                <input
                    type="checkbox"
                    id="open"
                    name="open"
                    checked
                    style="display: none;"
                >
                <input type="hidden"
                    id="embed"
                    name="embed"
                    onchange="if(this.value != '') { PublishBrief_ajaxEmbedLoading(); PublishBrief_ajaxEmbedTest(this.value); }"
                    {if="!empty($draft->links) && !empty($draft->links[0])"}
                         value="{$draft->links[0]}"
                    {elseif="$url"}
                        value="{$url}"
                    {/if}
                >
            </form>
        </li>
    </ul>

    <ul class="list middle">
        <li>
            <span class="primary icon gray bubble active privacy color"
                  title="{$c->__('post.public')}"
                  onclick="PublishBrief.togglePrivacy()">
                <i class="zmdi zmdi-portable-wifi"></i>
            </span>
            <span class="control icon active gray"
                title="{$c->__('publishbrief.post')}"
                onclick="MovimUtils.reload('{$c->route('publish')}')">
                <i class="zmdi zmdi-plus-circle"></i>
            </span>
            <p class="normal embed flex">
                {$embed}
            </p>
        </li>
    </ul>
</div>
