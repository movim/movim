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
                        placeholder="username@server.com"/>
                    <label for="login">{$c->__('form.username')}</label>
                </div>
                <div>
                    <input type="password" name="pass" id="pass" autocomplete="off" required disabled
                        placeholder="{$c->__('form.password')}"/>
                    <label for="pass">{$c->__('form.password')}</label>
                </div>
                <div>
                    <ul class="list thin">
                        <li>
                            <p class="center">
                                <a id="return_sessions" class="button flat" href="#" onclick="Login.toChoose()">
                                    {$c->__('account.title')}
                                </a>
                                <input
                                    type="submit"
                                    disabled
                                    data-loading="{$c->__('button.connecting')}"
                                    value="{$c->__('button.come_in')}"
                                    class="button flat"/>
                            </p>
                        </li>
                    </ul>
                </div>
            </form>

            {if="isset($info) && $info != ''"}
            <ul class="list thin card">
                <li class="info">
                    <p class="normal">{$info}</p>
                </li>
            </ul>
            {/if}

            {if="isset($whitelist) && $whitelist != ''"}
            <ul class="list thin card">
                <li class="info">
                    <p class="normal">{$c->__('whitelist.info')} : {$whitelist}</p>
                </li>
            </ul>
            {/if}

            <ul class="list thin">
                <li>
                    <p class="normal center">
                        {$c->__('form.no_account')}
                        <a class="button flat" href="{$c->route('account')}">
                            {$c->__('form.create_one')}
                        </a>
                    </p>
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
