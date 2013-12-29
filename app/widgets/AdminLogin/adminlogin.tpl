<form method="post" class="paddedtop">
    <fieldset>
        <div class="element">
            <label for="username">{$c->t('Username')}</label>
            <input type="text" name="username" class="content">
        </div>
        <div class="element">
            <label for="password">{$c->t('Password')}</label>
            <input type="password" name="password" class="content">
        </div>
        
        <input 
            class="button color green oppose" 
            type="submit" 
            
            name="submit" 
            value="{$c->t('Submit')}" />
    </fieldset>
</form>
