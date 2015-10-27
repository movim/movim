<div id="login_widget">
    <div id="sessions" class="dialog actions"></div>

    {if="isset($httpAuthUser)"}
        <script type="text/javascript">
        MovimWebsocket.attach(function() {
            MovimWebsocket.connection.register('{$httpAuthHost}');
        });
        MovimWebsocket.register(function() {
            Login_ajaxHTTPLogin('{$httpAuthUser}', '{$httpAuthPassword}');
        });
        </script>
    {/if}

    <div id="form" class="dialog">
        <section>
            <span class="info">{$c->__('form.connected')} {$connected} / {$pop}</span>
            <h3>{$c->__('page.login')}</h3>
            <form
                data-action="{$submit}"
                name="login">
                <div>
                    <input type="email" name="login" id="login" autofocus required disabled
                        placeholder="{$c->__('form.username')}"/>
                    <label for="login">{$c->__('form.username')}</label>
                </div>
                <div>
                    <input type="password" name="pass" id="pass" autocomplete="off" required disabled
                        placeholder="{$c->__('form.password')}"/>
                    <label for="pass">{$c->__('form.password')}</label>
                </div>
                <div>
                    <ul class="simple thin">
                        <li class="action">
                            <div class="action">
                                <input
                                    type="submit"
                                    disabled
                                    data-loading="{$c->__('button.connecting')}"
                                    value="{$c->__('button.come_in')}"
                                    class="button flat"/> 
                            </div>
                            <a id="return_sessions" class="button flat" href="#" onclick="Login.toChoose()">
                                {$c->__('account.title')}
                            </a>
                        </li>
                    </ul>
                </div>
            </form>

            {if="isset($info) && $info != ''"}
            <ul class="thin simple card">
                <li class="info">
                    <p>{$info}</p>
                </li>
            </ul>
            {/if}

            {if="isset($whitelist) && $whitelist != ''"}
            <ul class="thin simple card">
                <li class="info">
                    <p>{$c->__('whitelist.info')} : {$whitelist}</p>
                </li>
            </ul>
            {/if}

            <ul class="thin simple">
                <li class="new_account">
                    <span>{$c->__('form.no_account')}<br />
                        <a class="button flat" href="{$c->route('account')}">
                            {$c->__('form.create_one')}
                        </a>
                    </span>
                </li>
            </ul>
        </section>
    </div>

    <div id="error" class="dialog actions">
        {$error}
    </div>
</div>

<div id="error_websocket" class="snackbar">
    {$c->__('error.websocket')}
</div>
