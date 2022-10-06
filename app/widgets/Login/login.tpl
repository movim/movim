<div id="login_widget">
    <script type="text/javascript">
        if (typeof navigator.registerProtocolHandler == 'function') {
            navigator.registerProtocolHandler('xmpp',
                                          '{$c->route("share")}/%s',
                                          'Movim');
        }

        Login.domain = '{$domain}';
        {if="isset($httpAuthUser)"}
            localStorage.username = '{$httpAuthUser}';
            MovimWebsocket.attach(function() {
                MovimWebsocket.connection.register('{$httpAuthHost}');
            });
            MovimWebsocket.register(function() {
                Login_ajaxHTTPLogin('{$httpAuthUser}', '{$httpAuthPassword}');
            });
        {/if}
    </script>

    <div class="logo"></div>

    <div id="form" class="dialog">
        <section>
            {if="$banner"}
                <img class="banner" src="{$banner}">
            {/if}
            {if="$invitation != null"}
                <ul class="list middle invite">
                    <li>
                        {$url = $contact->getPhoto('m')}
                        {if="$url"}
                            <span class="primary icon bubble" style="background-image: url({$url});">
                            </span>
                        {else}
                            <span class="primary icon bubble color {$contact->jid|stringToColor}">
                                {$contact->truename|firstLetterCapitalize}
                            </span>
                        {/if}
                        {if="$invitation->room && $invitation->room->getPhoto('m')"}
                            <span class="primary icon bubble" style="background-image: url({$invitation->room->getPhoto('m')});">
                            </span>
                        {/if}
                        <div>
                            <p></p>
                            <p class="all">
                                {$c->__('form.invite_chatroom', $contact->truename)}:
                                <a href="xmpp:{$invitation->resource}?join">{$invitation->resource}</a>
                            </p>
                        </div>
                    </li>
                </ul>
            {/if}

            <form
                method="post" action="login"
                name="login">
                <div>
                    <input type="text" id="complete" tabindex="-1"/>
                    <input type="text" pattern="^[^\u0000-\u001f\u0020\u0022\u0026\u0027\u002f\u003a\u003c\u003e\u0040\u007f\u0080-\u009f\u00a0]+@[a-z0-9.-]+\.[a-z]{2,10}$" name="username" id="username" autofocus required
                        placeholder="username@server.com"/>
                    <label for="username">{$c->__('form.username')}</label>
                </div>
                <div>
                    <input type="password" name="password" id="password" required
                        placeholder="{$c->__('form.password')}"/>
                    <label for="password">{$c->__('form.password')}</label>
                </div>

                <ul class="list thin">
                    <li class="info">
                        <div>
                            <p></p>
                            <p class="center">
                                {if="!empty($whitelist)"}
                                    {$c->__('form.whitelist_info')} :
                                    {loop="$whitelist"}
                                        {$value}
                                    {/loop}
                                {else}
                                    {$c->__('form.connect_info')}
                                {/if}
                            </p>
                        </div>
                    </li>
                </ul>

                <div>
                    <ul class="list thin">
                        <li>
                            <div>
                                <p class="center">
                                    <input
                                        type="submit"
                                        disabled
                                        data-loading="{$c->__('button.connecting')}…"
                                        value="{$c->__('page.login')}"
                                        class="button color"/>
                                    {if="!App\Configuration::get()->disableregistration"}
                                        <a class="button flat" href="{$c->route('account')}">
                                            {$c->__('button.sign_up')}
                                        </a>
                                    {/if}
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </form>
        </section>
    </div>

    <div id="form" class="dialog more">
        <section>
            <span class="info">{$c->__('form.connected')}: {$connected} - {$c->__('form.population')}: {$pop}</span>
            {if="$admins->count() > 0"}
                <ul class="list thin active">
                    <li class="subheader">
                        <div>
                            <p>
                                {$c->__('form.pod_admins')}
                            </p>
                        </div>
                    </li>
                    {loop="$admins"}
                        {$contact = $value->contact}
                        <li class="block" onclick="MovimUtils.redirect('{$c->route('blog', $value->resolvedNickname)}')">
                            <span class="control gray icon">
                                <i class="material-icons">chevron_right</i>
                            </span>
                            {$url = null}
                            {if="$contact"}
                                {$url = $contact->getPhoto()}
                            {/if}
                            {if="$url"}
                                <span class="primary icon bubble small">
                                    <img src="{$url}">
                                </span>
                            {else}
                                <span class="primary icon bubble small color {$value->id|stringToColor}">
                                    <i class="material-icons">person</i>
                                </span>
                            {/if}
                            <div>
                                <p class="line normal" title="{$value->resolvedNickname}">
                                    {if="$contact"}
                                        {$contact->truename}<span class="second">{$value->resolvedNickname}</span>
                                    {else}
                                        {$value->resolvedNickname}
                                    {/if}
                                </p>
                            </div>
                        </li>
                    {/loop}
                </ul>
            {/if}

            {if="isset($info)"}
                <ul class="list thin card">
                    <li class="info">
                        <div>
                            <p></p>
                            {autoescape="off"}
                                {$info}
                            {/autoescape}
                        </div>
                    </li>
                </ul>
            {/if}

            <ul class="list middle hide" id="pwa">
                <li class="block active">
                    <span class="primary icon bubble gray">
                        <i class="material-icons on_desktop">install_desktop</i>
                        <i class="material-icons on_mobile">install_mobile</i>
                    </span>
                    <div>
                        <p class="line">{$c->__('apps.install')}<p>
                        <p class="all">
                            {$c->__('apps.install_text')}
                        </p>
                    </div>
                </li>
            </ul>
        </section>
    </div>

    <div id="error" class="dialog actions">
        {autoescape="off"}
            {$error}
        {/autoescape}
    </div>
</div>
