<div>
    <ul class="simple thick">
        <li>
            <form method="post">
                    <div>
                        <input type="text" name="username" class="content" placeholder="{$c->__('input.username')}">
                        <label for="username">{$c->__('input.username')}</label>
                    </div>
                    <div>
                        <input type="password" name="password" class="content" placeholder="{$c->__('input.password')}">
                        <label for="password">{$c->__('input.password')}</label>
                    </div>
                    
                    <input 
                        class="button oppose color" 
                        type="submit" 
                        name="submit" 
                        value="{$c->__('button.validate')}" />
            </form>
        </li>
    </ul>
</div>
