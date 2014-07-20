<form method="post" class="paddedtop">
    <fieldset>
        <div class="element">
            <label for="username">{$c->__('input.username')}</label>
            <input type="text" name="username" class="content">
        </div>
        <div class="element">
            <label for="password">{$c->__('input.password')}</label>
            <input type="password" name="password" class="content">
        </div>
        
        <input 
            class="button color green oppose" 
            type="submit" 
            name="submit" 
            value="{$c->__('button.submit')}" />
    </fieldset>
</form>
