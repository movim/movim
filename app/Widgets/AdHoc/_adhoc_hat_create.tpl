<section class="scroll">
    <form name="command"
          data-sessionid="{$attributes->sessionid}"
          data-node="{$attributes->node}"
          onsubmit="return false;">
        <input type="hidden" name="FORM_TYPE" value="urn:xmpp:hats:commands">

        <div>
            <input id="hat_title" name="hats#title" type="text"
                   placeholder="{$c->__('hats.title_placeholder')}"
                   oninput="AdHoc.hatCreate.updatePreview()"
                   required>
            <label for="hat_title">{$c->__('hats.title_field')}</label>
        </div>

        <div>
            <input id="hat_uri" name="hats#uri" type="text" value="{$uri}" readonly>
            <label for="hat_uri">{$c->__('hats.uri')}</label>
        </div>

        <div class="compact">
            <br>
            <input type="color" id="hat_color_picker" value="{$hueHex}"
                   oninput="AdHoc.hatCreate.onColorChange(this.value)"
                   style="width:100%;height:5rem;padding:0;border:none;cursor:pointer;background:none;">
            <input type="hidden" id="hat_hue" name="hats#hue" value="{$hue}">
            <label>{$c->__('hats.color')}</label>
        </div>

        <div>
            <p>
                <span class="chip thin" id="hat_preview">
                    <i class="material-symbols fill icon" id="hat_preview_icon"
                       style="color:{$hueHex}">circle</i>
                    <span id="hat_preview_title">…</span>
                </span>
            </p>
            <label>{$c->__('hats.preview')}</label>
        </div>
    </form>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
    <button id="adhoc_action" class="button flat disabled" data-jid="{$jid}"
            onclick="AdHoc.submit(this.dataset.jid, 'complete')">
        {$c->__('button.submit')}
    </button>
</footer>
