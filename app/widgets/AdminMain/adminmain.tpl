<form name="admin" id="adminform" action="#" method="post">
    <div id="admingen" class="tabelem paddedtop" title="{$c->__('admin.general')}">
    <fieldset>
        <legend>{$c->__('admin.general')}</legend>

        <div class="element">
            <label for="da">{$c->__('general.language')}</label>
            <div class="select">
                <select id="locale" name="locale">
                    <option value="en">English (default)</option>';
                    {loop="$langs"}               
                        <option value="{$key}"
                        {if="$conf->locale == $key"}
                            selected="selected"
                        {/if}>
                            {$value}
                        </option>
                    {/loop}
                </select>
            </div>
        </div>

        <div class="element">
            <label for="da">{$c->__('general.environment')}</label>
            <div class="select">
                <select id="environment" name="environment">';
                    {loop="$envs"}               
                        <option value="{$key}"
                        {if="$conf->environment == $key"}
                            selected="selected"
                        {/if}>
                            {$value}
                        </option>
                    {/loop}
                </select>
            </div>
        </div>


        <div class="element">
            <label for="sizelimit">{$c->__('general.limit')}</label>
            <input type="text" name="sizelimit" id="sizelimit" value="{$conf->sizelimit}" />
        </div>

        <div class="element">
            <label for="loglevel">{$c->__('general.log_verbosity')}</label>
            <div class="select">
                <select id="loglevel" name="loglevel">
                    {loop="$logs"}               
                        <option value="{$key}"
                        {if="$conf->loglevel == $key"}
                            selected="selected"
                        {/if}>
                            {$value}
                        </option>
                    {/loop}
          </select>
            </div>
        </div>

        <div class="element">
            <label for="timezone">{$c->__('general.timezone')}</label>
            <div class="select">
                <select id="timezone" name="timezone">
                    {loop="$timezones"}
                        <option value="{$key}"
                        {if="$conf->timezone == $key"}
                            selected="selected"
                        {/if}>
                            {$value}
                        </option>
                    {/loop}
                </select>
            </div>
            <br /><br />
            <span class="dTimezone">{$c->date($conf->timezone)}</span>
        </div>
    </fieldset>
        

    <fieldset>
        <legend>{$c->__('bosh.title')}</legend>
            <div class="clear"></div>
            
            <p>
                {$c->__('bosh.info1')}<br />
                {$bosh_info4}
            </p>
            
            {if="!$c->testBosh($conf->boshurl)"}
                <div class="message error">
                    {$c->__('bosh.not_recheable')}
                </div>
            {/if}

            <div class="element">
                <label for="boshurl">{$c->__('bosh.label')}</label>
                <input type="text" id="boshurl" name="boshurl" value="{$conf->boshurl}"/>
            </div>

            {if="isset($boshs)"}
                <div class="element simple">
                    <label for="boshurl">
                        {$c->__('bosh.publics')} -
                        <a target="_blank" href="https://api.movim.eu/">Movim API</a>
                    </label>

                    <dl>
                        {loop="$boshs->boshs"}
                            <dt>{$value->name}</dt>
                            <dd>{$value->url}</dd>
                        {/loop}
                    </dl>
                </div>
            {/if}
    </fieldset>


    <fieldset>
        <legend>{$c->__('whitelist.title')}</legend>
        <div class="clear"></div>                

        <div class="element large">
            <label for="xmppwhiteiist">{$c->__('whitelist.label')}</label>
            <p>{$c->__('whitelist.info1')}</p>
            <p>{$c->__('whitelist.info2')}</p>
            <input type="text" name="xmppwhitelist" id="xmppwhitelist" value="{$conf->xmppwhitelist}" />
        </div>
    </fieldset>


    <fieldset>
        <legend>{$c->__('information.title')}</legend>
        <div class="clear"></div>

        <div class="element large">
            <label for="description">{$c->__('information.description')}</label>
            <textarea type="text" name="description" id="description" />{$conf->description}</textarea>
        </div>
        <div class="clear"></div>

        <div class="element large">
            <label for="info">{$c->__('information.label')}</label>
            <p>{$c->__('information.info1')}</p>
            <p>{$c->__('information.info2')}</p>
            <textarea type="text" name="info" id="info" />{$conf->info}</textarea>
        </div>
    </fieldset>


    <fieldset>
        <legend>{$c->__('credentials.title')}</legend>
            
        {if="$conf->user == 'admin' || $conf->pass == sha1('password')"}
            <div class="message error">
                {$c->__('credentials.info')}
            </div>
        {/if}

        <div class="element" >
            <label for="username">{$c->__('credentials.username')}</label>
            <input type="text" id="username" name="username" value="{$conf->username}"/>
        </div>
        <div class="clear"></div>
        
        <div class="element">
            <label for="password">{$c->__('credentials.password')}</label>
            <input type="password" id="password" name="password" value=""/>
        </div>                            
        <div class="element">
            <label for="repassword">{$c->__('credentials.re_password')}</label>
            <input type="password" id="repassword" name="repassword" value=""/>
        </div>
    </fieldset>

    <input 
    type="submit" 
    class="button color green oppose" 
    value="{$c->__('button.submit')}"/>
    <div class="clear"></div>
    </div>
</form>
