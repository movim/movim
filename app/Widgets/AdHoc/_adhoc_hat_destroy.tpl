<section class="scroll">
    <h3>{$c->__('hats.destroy')}</h3>
    <form name="command"
          data-sessionid="{$attributes->sessionid}"
          data-node="{$attributes->node}"
          onsubmit="return false;">
        <input type="hidden" name="FORM_TYPE" value="urn:xmpp:hats:commands">
        <input type="hidden" id="hat_selected_field" name="" value="">

        {if="empty($hats)"}
            <div class="placeholder">
                <i class="material-symbols">local_police</i>
                <h1>{$c->__('hats.empty')}</h1>
            </div>
        {else}
            <ul class="list middle">
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
                            <p class="line second">{$value['value']}</p>
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
