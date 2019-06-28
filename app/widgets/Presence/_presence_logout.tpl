<section>
    <h3>{$c->__('status.disconnect')}</h3>
    <br />
    <h4 class="gray">{$c->__('status.logout_confirm')}</h4>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat"
        onclick="Presence_ajaxLogout()">
        {$c->__('status.disconnect')}
    </button>
</div>
