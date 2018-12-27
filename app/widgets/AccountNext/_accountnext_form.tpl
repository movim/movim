<form name="data">
    {autoescape="off"}
        {$formh}
    {/autoescape}
    <button
        type="button"
        class="button color oppose"
        onclick="{$submitdata}"
    >
        {$c->__('button.validate')}
    </button>
</form>
