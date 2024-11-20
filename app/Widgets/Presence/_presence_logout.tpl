<section>
    <div class="placeholder">
        <i class="material-symbols">exit_to_app</i>
        <h4>{$c->__('status.logout_confirm')}</h4>
    </div>
</section>
<footer>
    <button onclick="Dialog_ajaxClear()" class="button flat">
        {$c->__('button.cancel')}
    </button>
    <button
        name="submit"
        class="button flat"
        onclick="Presence_ajaxLogout()">
        {$c->__('status.disconnect')}
    </button>
</footer>
