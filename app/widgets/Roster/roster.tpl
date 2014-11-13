<div id="roster" ng-controller="RosterController as rosterCtrl">
    <input type="text" name="search" id="rostersearch" autocomplete="off" placeholder="{$c->__('roster.search');}"/>
        
    <ul id="rosterlist" class="{{rosterCtrl.offlineIsShown()}}">
        <span ng-hide="contacts.length" class="nocontacts">
            {$c->__('roster.no_contacts')}
            <br />
            <br />
            <a class="button color green icon users" href="{$c->route('explore')}">{$c->__('page.explore')}</a>
        </span>

        <div ng-show="contacts.length" ng-repeat="group in contacts" id="group{{group.agroup}}" ng-class="{groupshown: rosterCtrl.groupIsShown(group.agroup) == 'true'}">
            <h1 ng-click="rosterCtrl.showHideGroup(group.agroup)">{{group.agroup}}</h1>
            <!--ng-hide="myjid.tombstone == 'true'" -->
            <li ng-repeat="myjid in group.agroupitems" id="{{myjid.ajid}}" class="{{myjid.ajiditems[0].rosterview.presencetxt}}" ng-attr-title="{{rosterCtrl.getContactTitle(myjid.ajiditems[0])}}">
                <!-- Rostersearch look this way for an angularJS solution http://www.bennadel.com/blog/2487-filter-vs-nghide-with-ngrepeat-in-angularjs.htm -->
                <ul class="contact">
                    <li ng-repeat="contact in myjid.ajiditems" class="{{contact.rosterview.presencetxt}} {{contact.rosterview.inactive}}" ng-class="rosterCtrl.getContactClient(contact)" >
                        <!-- add title to li so search works again-->
                        <div class="chat on" ng-click="rosterCtrl.postChatAction(contact)" ></div>
                        <div ng-if="contact.rosterview.type == 'handheld'" class="infoicon mobile"></div>
                        <div ng-if="contact.rosterview.type == 'web'" class="infoicon web"></div>
                        <div ng-if="contact.rosterview.type == 'bot'" class="infoicon bot"></div>
                        <div ng-if="contact.rosterview.tune" class="infoicon tune"></div>
                        <div
                            ng-if="contact.rosterview.jingle"
                            class="infoicon jingle"
                            ng-click="rosterCtrl.postJingleAction(contact)">
                        </div>

                        <a href="{{contact.rosterview.friendpage}}">
                            <img
                                class="avatar"
                                src="{{contact.rosterview.avatar}}"
                                alt="avatar"
                            />
                            {{contact.rosterview.name}}
                            <span class="ressource">
                                <span ng-if="contact.status != ''">{{contact.status}} -</span>
                                 {{contact.ressource}}
                            </span>
                        </a>
                    </li>
                </ul>
            </li>
        </div>
    </ul>
</div>

<div id="rostermenu" class="menubar" ng-controller="RosterMenuController as rosterMenuCtrl">
    <ul class="menu">
        <li 
            class="show_hide body_infos on_mobile"
            onclick="
                movim_remove_class('body', 'roster'),
                movim_toggle_class('body', 'infos')"
            title="{$c->__('roster.show_hide')}">
            <a class="about" href="#"></a>
        </li>

        <li class="on_mobile">
            <a class="conf" title="{$c->__('page.configuration')}" href="{$c->route('conf')}">
            </a>
        </li>
        <li class="on_mobile">
            <a class="help" title="{$c->__('page.help')}" href="{$c->route('help')}">
            </a>
        </li>

        <li 
            class="show_hide body_roster on_mobile"
            onclick="
                movim_remove_class('body', 'infos'),
                movim_toggle_class('body', 'roster')"
            title="{$c->__('roster.show_hide')}">
            <a class="down" href="#"></a>
        </li>

        <li title="{$c->__('button.add')}">
            <label class="plus" for="addc"></label>
            <input type="checkbox" id="addc"/>
            <div class="tabbed">    
                <div class="message">                  
                    {$c->__('roster.add_contact_info1')}<br />
                    {$c->__('roster.add_contact_info2')}
                </div>  
                <input 
                    name="searchjid" 
                    class="tiny" 
                    type="email"
                    title="{$c->__('roster.jid')}"
                    placeholder="user@server.tld"
                    ng-keypress="rosterMenuCtrl.checkoutAddJid(event=$event)"
                />

            </div>
        </li>

        <li 
            onclick="showHideOffline()"
            title="{$c->__('roster.show_hide')}">
            <a class="users" href="#"></a>
        </li>

    </ul>
</div>
