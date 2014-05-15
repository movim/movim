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
            class="connectform"
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
            
            <p class="create">
                <a class="button color transparent oppose icon user" href="{$c->route('account')}">
                    {$c->t('Create one !')}
                </a>
                <span>{$c->t('No account yet ?')}</span>
            </p>

            <div class="clear"></div>
        
            <ul id="loginhelp">
                {if="$whitelist_display == true"}
                    <li id="whitelist">
                        <p>{$c->t('This server accept only connection with xmpp accounts from these servers :')}</p>
                        <p style="font-weight:bold; text-align:center; margin:0.5em;">{$whitelist}</p>
                        <p>{$c->t("If you don't have such xmpp account, you can try %sanother public Movim%s client.", '<a href="http://pod.movim.eu">', '</a>')}</p>
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

            <div class="clear"></div>

        </form>
    {/if}

    <div class="admin">
        {$c->t('Connected')} {$connected} • {$c->t('Population')} {$pop} • 
        <a href="{$c->route('admin')}">
            {$c->t('Administration')}
        </a>
    </div>
</div>
