<form name="data">
    <div id="movim">
        <span>@movim.eu</span>
        <label for="username">{$c->__('input.username')}</label>
        <input name="username" type="text" placeholder="username">
    </div>
    <div>
        <label for="password">{$c->__('input.password')}</label>
        <input name="password" type="password" placeholder="{$c->__('input.password')}">
    </div>
    <div class="compact">
        <input name="re_password" type="password" placeholder="{$c->__('credentials.re_password')}">
    </div>
    <a
        class="button color green oppose"
        onclick="{$submitdata}"
    >
        {$c->__('button.validate')}
    </a>
</form>
