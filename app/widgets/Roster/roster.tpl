<div id="roster" ng-controller="RosterController as rosterCtrl">  
    <ul id="rosterlist" class="offlineshown active all">
        <span ng-if="contacts == null" class="nocontacts">
            {$c->__('roster.no_contacts')}
            <br />
            <br />
            <a class="button color green" href="{$c->route('explore')}"><i class="fa fa-compass"></i> {$c->__('page.explore')}</a>
        </span>
        
        <li class="subheader search">{$c->__('roster.results')}</li>
        <div ng-if="contacts != null && !group.tombstone" ng-repeat="group in contacts" id="group{{group.agroup}}" ng-class="{groupshown: rosterCtrl.groupIsShown(group.agroup)}" >
            <li class="subheader" ng-click="rosterCtrl.showHideGroup(group.agroup)">{{group.agroup}}</li>
            <li ng-repeat="myjid in group.agroupitems" ng-if="!myjid.tombstone" id="{{myjid.ajid}}" class="{{myjid.ajiditems[0].rosterview.presencetxt}}" ng-attr-title="{{rosterCtrl.getContactTitle(myjid.ajiditems[0])}}">
                <!-- Rostersearch look this way for an angularJS solution http://www.bennadel.com/blog/2487-filter-vs-nghide-with-ngrepeat-in-angularjs.htm -->
                <ul class="contact active">
                    <li ng-repeat="contact in myjid.ajiditems" class="{{contact.rosterview.presencetxt}} {{contact.rosterview.inactive}}" ng-class="{condensed: contact.status != '' && contact.status != null }" ng-class="rosterCtrl.getContactClient(contact)" >
                        <div class="control" ng-switch on="contact.rosterview.type">
                            <i ng-switch-when="handheld" class="md md-smartphone"></i>
                            <i ng-switch-when="web" class="md md-language"></i>
                            <i ng-switch-when="bot" class="md md-memory"></i>
                            <i ng-if="contact.rosterview.tune" class="md md-play-arrow"></i>
                        </div>

                        <span class="icon bubble">
                            <img
                                class="avatar"
                                ng-src="{{::contact.rosterview.avatar}}"
                                alt="avatar"
                            />
                        </span>
                        <div class="chat on"></div>
                        <!--
                        MOVE IT TO CONTACT PAGE BOTTOM RIGHT BUTTON:
                        ng-click="rosterCtrl.postChatAction(contact)"
                            Also this means we can remove:
                            - postChatAction() from roster.js
                            - c.rosterview.openchat from the contact object sent from php
                        -->
                        <!--<div
                            ng-if="contact.rosterview.jingle"
                            class="infoicon jingle"
                            ng-click="rosterCtrl.postJingleAction(contact)">
                        </div>-->
                        {{contact.rosterview.name}}
                        <p class="wrap">
                            <span ng-if="contact.status != ''">{{contact.status}}</span>
                        </p>
                    </li>
                </ul>
            </li>
        </div>
    </ul>
</div>
