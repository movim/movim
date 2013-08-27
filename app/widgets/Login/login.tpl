<div id="loginpage" class="fadeDown">

    {if="!BROWSER_COMP"}
        <div class="message warning">
            {$c->t('Your web browser is too old to use with Movim.')}
        </div> ';
    {else}
        {if="isset($conf.info) && $conf.info != ''"}
            <div class="message warning">
                {$conf.info}
            </div>
        {/if}
        <form
            name="login"
            id="connectform"
            target="passwordiframe"
            method="POST"
            action="blank.php"
            onkeypress="
                if(event.keyCode == 13) {
                    {$submit_event}
                }"
            >
            <div class="element">
                <input type="email" name="login" id="login" autofocus required
                    placeholder="{$c->t('My address')}"/>
            </div>
            <div class="element">
                <input type="password" name="pass" id="pass" required
                    placeholder="{$c->t("Password")}"/>
            </div>
            <div class="element login">
                <a
                    class="button color green icon yes"
                    onclick="{$submit_event}"
                    id="submit"
                    name="submit">{$c->t("Come in!")}</a>
            </div>
            
            <input type="submit" id="submitb" name="submitb" value="submit" style="display: none;"/> 
            
            <div class="clear"></div>
            
            <ul id="loginhelp">
                {if="$whitelist_display == true"}
                    <li id="whitelist">
                        <p>This server accept only connection with xmpp accounts from these servers :</p>
                        <p style="font-weight:bold; text-align:center; margin:0.5em;">{$whitelist}</p>
                        <p>If you don\'t have such xmpp account, you can try <a href="http://pod.movim.eu">another public Movim</a> client.</p>
                    </li>
                {else}
                    <li id="jabber">{$c->t('You can login using your favorite Jabber account')}
                        <a href="#" onclick="fillExample('demonstration@movim.eu', 'demonstration');">
                            {$c->t('or with our demonstration account')}
                        </a>
                    </li>
                    <li id="gmail">
                        {$gmail}
                    </li>
                    <li id="facebook">
                        {$facebook}
                    </li>
                {/if}
            </ul>
            
            <iframe id="passwordiframe" name="passwordiframe" style="display: none;"></iframe>
            
            <div id="warning">{$warnings}</div>
            <div class="infos">
                    {$c->t('Population')} {$pop} • 
                    {$c->t('No account yet ?')}
                    <a href="{$c->route('account')}">
                        {$c->t('Create one !')}
                    </a>
            </div>
            <div class="clear"></div>

        </form>
    {/if}

    <div class="admin">
        <a href="{$c->route('admin')}">
            {$c->t('Administration')}
        </a>
    </div>
</div>
