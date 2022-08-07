<div id="admingen" class="tabelem padded_top_bottom" title="{$c->__('admin.general')}" data-mobileicon="manage_accounts">
<form name="admin" id="adminform" action="#" method="post">
    <input type="hidden" name="adminform" id="adminform" value="true"/>
    <div>
        <label for="da">{$c->__('general.language')}</label>
        <div class="select">
            <select id="locale" name="locale">
                <option value="en">English (default)</option>
                {loop="$langs"}
                    <option value="{$key}"
                            dir="auto"
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
        <textarea type="text" name="description" id="description" placeholder="{$c->__('information.description_placeholder')}"
                  onclick="MovimUtils.textareaAutoheight(this);"
                  oninput="MovimUtils.textareaAutoheight(this);"/>{$conf->description}</textarea>
        <label for="description">{$c->__('information.description')}</label>
    </div>
    <div class="clear"></div>

    <div>
        <input type="text" name="banner" id="banner" placeholder="http://server.tld/banner.jpg" value="{$conf->banner}" />
        <label for="description">{$c->__('information.banner')}</label>
    </div>

    <ul class="list thin">
        <li>
            <span class="primary icon bubble gray">
                <i class="material-icons">help</i>
            </span>
            <div>
                <p class="normal all">{$c->__('information.banner_info')}</p>
            </div>
        </li>
    </ul>

    <div class="clear"></div>

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
        <ul class="list thick">
            <li class="wide">
                <span class="control">
                    <div class="checkbox">
                        <input
                            {if="$conf->restrictsuggestions"}
                                checked
                            {/if}
                            type="checkbox"
                            id="restrictsuggestions"
                            name="restrictsuggestions"/>
                        <label for="restrictsuggestions"></label>
                    </div>
                </span>
                <div>
                    <p>{$c->__('restrictsuggestions.title')}</p>
                    <p class="all">{$c->__('restrictsuggestions.text')}</p>
                </div>
            </li>
            <li class="wide">
                <span class="control">
                    <div class="checkbox">
                        <input
                            {if="$conf->chatonly"}
                                checked
                            {/if}
                            type="checkbox"
                            id="chatonly"
                            name="chatonly"/>
                        <label for="chatonly"></label>
                    </div>
                </span>
                <div>
                    <p>{$c->__('chatonly.title')}</p>
                    <p class="all">{$c->__('chatonly.text')}</p>
                </div>
            </li>
            <li class="wide">
                <span class="control">
                    <div class="checkbox">
                        <input
                            {if="$conf->disableregistration"}
                                checked
                            {/if}
                            type="checkbox"
                            id="disableregistration"
                            name="disableregistration"/>
                        <label for="disableregistration"></label>
                    </div>
                </span>
                <div>
                    <p>{$c->__('disableregistration.title')}</p>
                    <p class="all">{$c->__('disableregistration.text')}</p>
                </div>
            </li>
        </ul>
    </div>

    <br />

    <h3>{$c->__('xmpp.title')}</h3>

    <div>
        <input type="text" name="xmppdomain" id="xmppdomain" placeholder="server.tld" value="{$conf->xmppdomain}" />
        <label for="xmppdomain">{$c->__('xmpp.domain')}</label>
    </div>

    <div>
        <textarea type="text" name="xmppdescription" id="xmppdescription" placeholder="{$c->__('xmpp.description')}" />{$conf->xmppdescription}</textarea>
        <label for="xmppdescription">{$c->__('xmpp.description')}</label>
    </div>

    <br />

    <h3>{$c->__('whitelist.title')}</h3>

    <div>
        <input type="text" name="xmppwhitelist" id="xmppwhitelist" placeholder="{$c->__('whitelist.label')}" value="{$conf->xmppwhitelist_string ?? ''}" />
        <label for="xmppwhitelist">{$c->__('whitelist.label')}</label>
    </div>

    <ul class="list thin">
        <li>
            <span class="primary icon bubble gray">
                <i class="material-icons">help</i>
            </span>
            <div>
                <p>{$c->__('whitelist.info1')}</p>
                <p>{$c->__('whitelist.info2')}</p>
            </div>
        </li>
    </ul>

    <br />
    <h3>{$c->__('information.title')}</h3>

    <div>
        <textarea type="text" name="info" id="info"
                  placeholder="{$c->__('information.label')}"
                  onclick="MovimUtils.textareaAutoheight(this);"
                  oninput="MovimUtils.textareaAutoheight(this);"/>{$conf->info}</textarea>
        <label for="info">{$c->__('information.label')}</label>
    </div>

    <ul class="list thick">
        <li>
            <span class="primary icon bubble gray">
                <i class="material-icons">help</i>
            </span>
            <div>
                <p>{$c->__('information.info1')}</p>
                <p>{$c->__('information.info2')}</p>
                <p>{$c->__('publish.content_text')}</p>
            </div>
        </li>
    </ul>

    <h3>{$c->__('tenor.title')}</h3>

    <div>
        <input type="text" name="gifapikey" id="gifapikey" placeholder="123ABC" value="{$conf->gifapikey ?? ''}" />
        <label for="info">{$c->__('tenor.label')}</label>
    </div>

    <ul class="list thick">
        <li>
            <span class="primary icon bubble gray">
                <i class="material-icons">gif_box</i>
            </span>
            <div>
                <p>{$c->__('tenor.info1')}</p>
                <p><a href="https://tenor.com/" target="_blank">{$c->__('tenor.info2')}</a></p>
            </div>
        </li>
    </ul>

    <h3>{$c->__('twitter.title')}</h3>

    <div>
        <input type="text" name="twittertoken" id="twittertoken" placeholder="123ABC" value="{$conf->twittertoken ?? ''}" />
        <label for="info">{$c->__('twitter.label')}</label>
    </div>

    <ul class="list thick">
        <li>
            <span class="primary icon bubble gray">
                <i class="material-icons">text_snippet</i>
            </span>
            <div>
                <p>{$c->__('twitter.info1')}</p>
                <p><a href="https://developer.twitter.com/" target="_blank">{$c->__('twitter.info2')}</a></p>
            </div>
        </li>
    </ul>

    <input
    type="submit"
    class="button color oppose"
    value="{$c->__('button.save')}"/>
    <div class="clear"></div>

    <br />
</form>
</div>
