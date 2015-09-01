<div id="admingen" class="tabelem padded_top_bottom" title="{$c->__('admin.general')}">
<form name="admin" id="adminform" action="#" method="post">
    <br />
    <h3>{$c->__('admin.general')}</h3>
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

    <!--
    <div>
        <input type="text" name="sizelimit" id="sizelimit" value="{$conf->sizelimit}" />
        <label for="sizelimit">{$c->__('general.limit')}</label>
    </div>
    -->
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

    <br />

    <h3>{$c->__('whitelist.title')}</h3>

    <div>
        <input type="text" name="xmppwhitelist" id="xmppwhitelist" placeholder="{$c->__('whitelist.label')}" value="{$conf->xmppwhitelist}" />
        <label for="xmppwhitelist">{$c->__('whitelist.label')}</label>
    </div>

    <ul class="thick">
        <li class="condensed">
            <span class="icon bubble color blue">
                <i class="zmdi zmdi-info"></i>
            </span>
            <p>{$c->__('whitelist.info1')}</p>
            <p>{$c->__('whitelist.info2')}</p>
        </li>
    </ul>

    <br />
    <h3>{$c->__('information.title')}</h3>

    <div>
        <textarea type="text" name="description" id="description" placeholder="{$conf->description}"/>{$conf->description}</textarea>
        <label for="description">{$c->__('information.description')}</label>
    </div>
    <div class="clear"></div>

    <div>
        <textarea type="text" name="info" id="info" placeholder="{$c->__('information.label')}" />{$conf->info}</textarea>
        <label for="info">{$c->__('information.label')}</label>
    </div>

    <ul class="thick">
        <li class="condensed">
            <span class="icon bubble color blue">
                <i class="zmdi zmdi-info"></i>
            </span>
            <span>{$c->__('information.info1')}</span>
            <p>{$c->__('information.info2')}</p>
        </li>
    </ul>

    {if="$server_rewrite"}
        <br />
        <h3>{$c->__('rewrite.title')}</h3>


        <div>
            <ul class="thick simple">
                <li class="action">
                    <div class="control action">
                        <div class="checkbox">
                            <input
                                type="checkbox"
                                id="rewrite"
                                name="rewrite"
                                {if="$conf->rewrite"}
                                    checked
                                {/if}>
                            <label for="rewrite"></label>
                        </div>
                    </div>
                    <span>{$c->__('rewrite.info')}</span>
                </li>
            </ul>
        </div>
    {/if}

    <br />
    <h3>{$c->__('credentials.title')}</h3>

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
    value="{$c->__('button.save')}"/>
    <div class="clear"></div>
</form>
</div>
