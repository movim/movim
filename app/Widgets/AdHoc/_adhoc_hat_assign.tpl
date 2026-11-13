<section class="scroll">
    <h3>{if="$isRemove"}{$c->__('hats.remove_from_user')}{else}{$c->__('hats.assign_to_user')}{/if}</h3>
    <form name="command"
          data-sessionid="{$attributes->sessionid}"
          data-node="{$attributes->node}"
          onsubmit="return false;">
        <input type="hidden" name="FORM_TYPE" value="urn:xmpp:hats:commands">
        <input type="hidden" id="hat_selected_field" name="" value="">

        <div>
            <input id="hat_jid" name="{$jidVar}" type="email"
                   placeholder="user@example.org"
                   oninput="AdHoc.checkFormValidity()"
                   required>
            <label for="hat_jid">{$c->__('hats.jid')}</label>
        </div>

        {if="empty($hats)"}
            <div class="placeholder">
                <i class="material-symbols">local_police</i>
                <h1>{$c->__('hats.empty')}</h1>
            </div>
        {else}
            <ul class="list middle">
                <li class="subheader">
                    <div><p>{$c->__('hats.pick')}</p></div>
                </li>
                {loop="$hats"}
                    <li class="active hat-pick-item"
                        data-fieldvar="{$value['fieldVar']}"
                        data-value="{$value['value']}"
                        onclick="AdHoc.hatPick(this)">
                        <span class="primary icon color {$value['color']}">
                            <i class="material-symbols fill">local_police</i>
                        </span>
                        <span class="control icon gray">
                            <i class="material-symbols">chevron_right</i>
                        </span>
                        <div>
                            <p class="line">{$value['label']}</p>
                        </div>
                    </li>
                {/loop}
            </ul>
        {/if}
    </form>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.close')}
    </button>
    {if="!empty($hats)"}
        <button id="adhoc_action" class="button flat disabled" data-jid="{$jid}"
                onclick="AdHoc.submit(this.dataset.jid, 'complete')">
            {$c->__('button.submit')}
        </button>
    {/if}
</footer>
