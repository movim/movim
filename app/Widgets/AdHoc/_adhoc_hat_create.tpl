<section class="scroll">
  <form name="command"
        data-sessionid="{$attributes->sessionid}"
        data-node="{$attributes->node}"
        onsubmit="return false;">
    <input type="hidden" name="FORM_TYPE" value="urn:xmpp:hats:commands">
    <input type="hidden" name="hats#uri" id="hat_uri" value="{$uri}">
    <input type="hidden" name="hats#hue" id="hat_hue" value="0">
    <div>
      <input id="hat_title" name="hats#title" type="text"
             placeholder="{$c->__('hats.title_placeholder')}"
             oninput="AdHoc.hatCreate.updatePreview()"
             required>
      <label for="hat_title">{$c->__('hats.title_field')}</label>
    </div>
    <div>
      <div style="display:flex;align-items:center;gap:1rem;padding:0.8rem 0 0.2rem 0;">
        <input type="color" id="hat_color_picker" value="{$hueHex}"
               oninput="AdHoc.hatCreate.onColorChange(this.value)"
               style="width:15%;height:3.5rem;padding:0;border:none;cursor:pointer;background:none;flex-shrink:0;">
        <span class="chip thin" id="hat_preview"
              style="display:flex;align-items:center;margin-left:2rem;">
          <i class="material-symbols fill icon" id="hat_preview_icon"
             style="color:{$hueHex}">circle</i>
          <span id="hat_preview_title">…</span>
        </span>
      </div>
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
