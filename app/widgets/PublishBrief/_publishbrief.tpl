<br class="on_desktop"/>
<div class="block">
    <ul class="list">
        <li>
            <span id="menu" class="primary on_mobile icon bubble active gray" onclick="MovimTpl.toggleMenu()">
                <i class="zmdi zmdi-menu"></i>
            </span>
            <span class="primary on_desktop icon bubble gray">
                <i class="zmdi zmdi-edit"></i>
            </span>
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
                        type="text"></textarea>
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
                    onchange="PublishBrief_ajaxEmbedTest(this.value)"
                    {if="$url"}value="{$url}"{/if}
                >
            </form>
        </li>
    </ul>

    <ul class="list middle">
        <li>
            <span class="primary icon gray bubble active privacy color"
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
