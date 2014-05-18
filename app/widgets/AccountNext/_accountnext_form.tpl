<form name="data">
    <fieldset>
        {$instr}

        <div class="clear"></div>
        {$formh}

        <input
            type="hidden"
            value="{$domain}"
            name="ndd"
            id="ndd"
        />

        <input
            type="hidden"
            value="{$ndd}"
            name="to"
            id="to"
        />

        <input
            type="hidden"
            value="{$id}"
            name="id"
            id="id"
        />

        <input
            id="submitb"
            type="submit"
            style="display: none;"
            value="submit"
            name="submitb"
        />

        <a
            class="button color green icon yes" 
            style="float: right;"
            onclick="
                localStorage.username = document.querySelector('#username').value+'@'+'{$ndd}';
                {$submitdata}"
        >
            {$c->__('button.validate')}
        </a>
    </fieldset>
</form>
