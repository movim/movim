<section>
    <h3>{$c->__('spaceinfo.invite_title')}</h3>
    <br />
    <h4 class="gray">{$c->__('spaceinfo.invite_text')}</h4>

    <form name="spacesinfo_invite">
        <div>
            <ul class="list">
                <li>
                    <span class="control icon active" onclick="MovimUtils.copyToClipboard('{$subscription->spaceURI}'); ChatActions_ajaxCopiedMessageText();">
                        <i class="material-symbols">content_copy</i>
                    </span>
                    <div>
                        <input name="title" value="{$subscription->spaceURI}" readonly/>
                        <label for="title">{$c->__('spaceinfo.invite_uri_title')}</label>
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
