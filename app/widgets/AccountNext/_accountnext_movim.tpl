<form name="data">
    <div id="movim">
        <span>@movim.eu</span>
        <label for="username">{$c->__('input.username')}</label>
        <input name="username" type="text" placeholder="username" pattern="^[^\u0000-\u001f\u0020\u0022\u0026\u0027\u002f\u003a\u003c\u003e\u0040\u007f\u0080-\u009f\u00a0]+$">
    </div>
    <div>
        <label for="password">{$c->__('input.password')}</label>
        <input name="password" type="password" placeholder="{$c->__('input.password')}">
    </div>
    <div class="compact">
        <input name="re_password" type="password" placeholder="{$c->__('credentials.re_password')}">
    </div>
    <a
        class="button color oppose"
        onclick="{$submitdata}"
    >
        {$c->__('button.validate')}
    </a>
</form>
