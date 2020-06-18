<form name="data">
    {autoescape="off"}
        {$formh}
    {/autoescape}
    <button
        type="button"
        class="button color oppose"
        onclick="AccountNext_ajaxRegister(MovimUtils.formToJson('data'))"
    >
        {$c->__('button.validate')}
    </button>
</form>
