<div id="admingen" class="tabelem" title="{$c->__('admin.general')}" data-mobileicon="manage_accounts">
<form name="admin" id="adminform" action="#" method="post">
    <input type="hidden" name="adminform" id="adminform" value="true"/>
    <div>
        <ul class="list middle">
            <li class="subheader">
                <div><p>{$c->__('config.general')}</p></div>
            </li>
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">language</i>
                </span>
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
            </li>
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">description</i>
                </span>
                <div>
                    <textarea type="text" name="description" id="description" placeholder="{$c->__('information.description_placeholder')}"
                              onclick="MovimUtils.textareaAutoheight(this);"
                              oninput="MovimUtils.textareaAutoheight(this);"/>{if="$conf->description"}{$conf->description}{/if}</textarea>
                    <label for="description">{$c->__('information.description')}</label>
                </div>
            </li>
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">image</i>
                </span>
                <div>
                    <input type="url" name="banner" id="banner" placeholder="http://server.tld/banner.jpg" value="{$conf->banner}" />
                    <label for="description">{$c->__('information.banner')}</label>
                    <span class="supporting"><i class="material-symbols">lightbulb</i> {$c->__('information.banner_info')}</span>
                </div>
            </li>
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">content_paste_search</i>
                </span>
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
            </li>
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">universal_local</i>
                </span>
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
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">chat</i>
                </span>
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
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">person_cancel</i>
                </span>
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
            <li class="subheader">
                <div><p>{$c->__('xmpp.title')}</p></div>
            </li>
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">domain</i>
                </span>
                <div>
                    <input type="text" name="xmppdomain" id="xmppdomain" placeholder="server.tld" value="{$conf->xmppdomain}" />
                    <label for="xmppdomain">{$c->__('xmpp.domain')}</label>
                </div>
            </li>
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">description</i>
                </span>
                <div>
                    <textarea type="text" name="xmppdescription" id="xmppdescription" placeholder="{$c->__('xmpp.description')}" />{$conf->xmppdescription}</textarea>
                    <label for="xmppdescription">{$c->__('xmpp.description')}</label>
                </div>
            </li>
            <li class="subheader">
                <div><p>{$c->__('whitelist.title')}</p></div>
            </li>
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">checklist_rtl</i>
                </span>
                <div>
                    <input type="text" name="xmppwhitelist" id="xmppwhitelist" placeholder="{$c->__('whitelist.label')}" value="{$conf->xmppwhitelist_string ?? ''}" />
                    <label for="xmppwhitelist">{$c->__('whitelist.label')}</label>
                    <span class="supporting"><i class="material-symbols">lightbulb</i> {$c->__('whitelist.info1')}</span>
                    <span class="supporting"><i class="material-symbols">lightbulb</i> {$c->__('whitelist.info2')}</span>
                </div>
            </li>
            <li class="subheader">
                <div><p>{$c->__('information.title')}</p></div>
            </li>
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols">help</i>
                </span>
                <div>
                    <textarea type="text" name="info" id="info"
                              placeholder="{$c->__('information.label')}"
                              onclick="MovimUtils.textareaAutoheight(this);"
                              oninput="MovimUtils.textareaAutoheight(this);"/>{$conf->info}</textarea>
                    <label for="info">{$c->__('information.label')}</label>
                    <span class="supporting"><i class="material-symbols">lightbulb</i> {$c->__('information.info1')}</span>
                    <span class="supporting"><i class="material-symbols">lightbulb</i> {$c->__('information.info2')} {$c->__('publish.content_text')}</span>
                </div>
            </li>
            <li class="subheader">
                <div><p>{$c->__('tenor.title')}</p></div>
            </li>
            <li>
                <span class="primary icon bubble gray">
                    <i class="material-symbols">ar_stickers</i>
                </span>
                <div>
                    <input type="text" name="gifapikey" id="gifapikey" placeholder="123ABC" value="{$conf->gifapikey ?? ''}" />
                    <label for="info">{$c->__('tenor.label')}</label>
                </div>
            </li>
            <li>
                <span class="primary icon bubble gray">
                    <i class="material-symbols">gif_box</i>
                </span>
                <div>
                    <p>{$c->__('tenor.info1')}</p>
                    <p><a href="https://tenor.com/" target="_blank">{$c->__('tenor.info2')}</a></p>
                </div>
            </li>
        </ul>
    </div>

    <input
    type="submit"
    class="button color oppose"
    value="{$c->__('button.save')}"/>
    <div class="clear"></div>

    <br />
</form>
</div>
