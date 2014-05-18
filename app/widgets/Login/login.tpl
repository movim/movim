<div id="loginpage" class="fadeDown">
    {if="!BROWSER_COMP"}
        <div class="message warning">
            {$c->__('error.too_old')}
        </div>
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
                    placeholder="{$c->__('form.username')}"/>
            </div>
            <div class="element">
                <input type="password" name="pass" id="pass" required
                    placeholder="{$c->__('form.password')}"/>
            </div>
            <div class="element login">
                <a
                    class="button color green icon yes"
                    onclick="{$submit_event}"
                    id="submit"
                    name="submit">{$c->__('button.come_in')}</a>
            </div>
            
            <input type="submit" id="submitb" name="submitb" value="submit" style="display: none;"/> 

            <div class="clear"></div>
            
            <p class="create">
                <a class="button color transparent oppose icon user" href="{$c->route('account')}">
                    {$c->__('form.create_one')}
                </a>
                <span>{$c->__('form.no_account')}</span>
            </p>

            <div class="clear"></div>
        
            <ul id="loginhelp">
                {if="$whitelist_display == true"}
                    <li id="whitelist">
                        <p>{$c->__('whitelist.info')}</p>
                        <p style="font-weight:bold; text-align:center; margin:0.5em;">{$whitelist}</p>
                        <p>{$c->__('whitelist.info2', '<a href="http://pod.movim.eu">', '</a>')}</p>
                    </li>
                {else}
                    <li id="jabber">{$c->__('account.jabber')}
                        <a href="#" onclick="fillExample('demonstration@movim.eu', 'demonstration');">
                            {$c->__('account.demo')}
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
        {$c->__('connected')} {$connected} • {$c->__('population')} {$pop} • 
        <a href="{$c->route('admin')}">
            {$c->__('page.administration')}
        </a>
    </div>
</div>
