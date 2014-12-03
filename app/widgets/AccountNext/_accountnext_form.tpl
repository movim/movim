<form name="data">
    <div id="subscription_error">

    </div>
    <fieldset>
        {$formh}
        <a
            class="button color green oppose" 
            onclick="{$submitdata}"
        >
            <i class="fa fa-check"></i> {$c->__('button.validate')}
        </a>
    </fieldset>
</form>
