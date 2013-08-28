<div id="roster" class="{$roster_show}">
    <input 
        type="text" 
        name="search" 
        id="request" 
       
        autocomplete="off" 
        onkeyup="rosterSearch(event);" 
        onclick="focusContact();" 
        placeholder="{$c->t('Search');}"/>
    <ul id="rosterlist" class="{$offline_shown}">
        {$c->prepareRoster()}
    </ul>
    <script type="text/javascript">sortRoster();</script>
</div>

<div id="rostermenu" class="menubar">
    <ul class="menu">
        <li 
            class="show_hide body_infos on_mobile"
            onclick="
                movim_remove_class('body', 'roster'),
                movim_toggle_class('body', 'infos')"
            title="{$c->t('Show/Hide')}">
            <a class="about" href="#"></a>
        </li>
        <li title="{$c->t('Add')}">
            <label class="plus" for="addc"></label>
            <input type="checkbox" id="addc"/>
            <div class="tabbed">    
                <div class="message">                  
                    {$c->t('Enter the Jabber ID of your contact.')}<br />
                    {$c->t('Press enter to validate.')}
                </div>  
                <input 
                    name="searchjid" 
                    class="tiny" 
                    type="email"
                    title="{$c->t('JID')}"
                    placeholder="user@server.tld" 
                    onkeypress="
                        if(event.keyCode == 13) {
                            {$search_contact}
                            return false;
                        }"
                />
            </div>
        </li>
        <li 
            onclick="{$toggle_cache}"
            title="{$c->t('Show/Hide')}">
            <a class="users" href="#"></a>
        </li>
        {if="$chatbox == true"}
            <div id="chattoggle">
                {$c->prepareChatToggle()}
            </div>
        {/if}
        <li 
            class="show_hide body_roster on_mobile"
            onclick="
                movim_remove_class('body', 'infos'),
                movim_toggle_class('body', 'roster')"
            title="{$c->t('Show/Hide')}">
            <a class="down" href="#"></a>
        </li>
    </ul>
</div>
