<section>
    <h3>{$c->__('spaceinfo.invite_title')}</h3>
    <br />
    <h4 class="gray">{$c->__('spaceinfo.invite_text')}</h4>

    <form name="spacesinfo_invite">
        <div>
            <ul class="list">
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">key</i>
                    </span>
                    <span class="control icon active" onclick="MovimUtils.copyToClipboard('{$subscription->uri}'); ChatActions_ajaxCopiedMessageText();">
                        <i class="material-symbols">content_copy</i>
                    </span>
                    <div>
                        <input name="title" value="{$subscription->uri}" readonly/>
                        <label for="title">{$c->__('spaceinfo.invite_uri_title')}</label>
                    </div>
                </li>
                <h2 style="text-align: center;">{$c->__('global.or')}</h2>
                <li>
                    <span class="primary icon gray">
                        <i class="material-symbols">link</i>
                    </span>
                    <span class="control icon active" onclick="Preview.copyToClipboard('{$inviteUrl}')">
                        <i class="material-symbols">content_copy</i>
                    </span>
                    <div>
                        <input readonly value="{$inviteUrl}">
                        <label>{$c->__('room.invite_code')}</label>
                    </div>
                </li>
            </ul>
        </div>
    </form>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
</footer>
