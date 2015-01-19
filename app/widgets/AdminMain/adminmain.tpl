<div id="admingen" class="tabelem paddedtop" title="{$c->__('admin.general')}">
<form name="admin" id="adminform" action="#" method="post">
    <legend><i class="fa fa-wrench"></i> {$c->__('admin.general')}</legend>

    <div>
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

    <div>
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


    <div>
        <input type="text" name="sizelimit" id="sizelimit" value="{$conf->sizelimit}" />
        <label for="sizelimit">{$c->__('general.limit')}</label>
    </div>

    <div>
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
        <label for="loglevel">{$c->__('general.log_verbosity')}</label>
    </div>

    <div>
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
        <label for="timezone">{$c->__('general.timezone')} - <span class="dTimezone">{$c->date($conf->timezone)}</span></label>
        <br /><br />

    </div>
    <legend><i class="fa fa-code"></i> {$c->__('websocket.title')}</legend>
    <div class="clear"></div>
    
    <p>
        {$c->__('websocket.info')}: <code>ws(s)://domain:port</code>
    </p>
    
    <!--{if="!$c->testBosh($conf->boshurl)"}
        <div class="message error">
            {$c->__('bosh.not_recheable')}
        </div>
    {/if}-->

    <div>
        <input type="text" id="websocketurl" name="websocketurl" placeholder="{$c->__('websocket.label')}" value="{$conf->websocketurl}"/>
        <label for="websocketurl">{$c->__('websocket.label')}</label>
    </div>

    {if="isset($websockets)"}
        <div class="element simple">
            <label for="websocketurl">
                {$c->__('websocket.publics')} -
                <a target="_blank" href="https://api.movim.eu/">Movim API</a>
            </label>

            <dl>
                {loop="$websockets->websockets"}
                    <dt>{$value->name}</dt>
                    <dd>{$value->url}</dd>
                {/loop}
            </dl>
        </div>
    {/if}
    
    <div class="message info block">
        <i class="fa fa-exclamation-triangle"></i> {$c->__('websocket.save_info')}
    </div>

    <legend><i class="fa fa-check-square-o"></i> {$c->__('whitelist.title')}</legend>
    <div class="clear"></div>                

    <div>
        <p>{$c->__('whitelist.info1')}</p>
        <p>{$c->__('whitelist.info2')}</p>
    </div>

    <div>
        <input type="text" name="xmppwhitelist" id="xmppwhitelist" placeholder="{$c->__('whitelist.label')}" value="{$conf->xmppwhitelist}" />
        <label for="xmppwhitelist">{$c->__('whitelist.label')}</label>
    </div>

    <legend><i class="fa fa-comment"></i> {$c->__('information.title')}</legend>
    <div class="clear"></div>

    <div>
        <textarea type="text" name="description" id="description" />{$conf->description}</textarea>
        <label for="description">{$c->__('information.description')}</label>
    </div>
    <div class="clear"></div>

    <div>
        <label for="info">{$c->__('information.label')}</label>
        <p>{$c->__('information.info1')}</p>
        <p>{$c->__('information.info2')}</p>
        <textarea type="text" name="info" id="info" />{$conf->info}</textarea>
    </div>

    <legend><i class="fa fa-user"></i> {$c->__('credentials.title')}</legend>
        
    {if="$conf->user == 'admin' || $conf->pass == sha1('password')"}
        <div class="message error">
            {$c->__('credentials.info')}
        </div>
    {/if}

    <div>
        <label for="username">{$c->__('credentials.username')}</label>
        <input type="text" id="username" name="username" value="{$conf->username}"/>
    </div>
    <div class="clear"></div>
    
    <div>
        <input type="password" id="password" name="password" value=""/>
        <label for="password">{$c->__('credentials.password')}</label>
    </div>                            
    <div>
        <input type="password" id="repassword" name="repassword" value=""/>
        <label for="repassword">{$c->__('credentials.re_password')}</label>
    </div>

    <input 
    type="submit" 
    class="button color green oppose" 
    value="{$c->__('button.submit')}"/>
    <div class="clear"></div>
</form>
    </div>
