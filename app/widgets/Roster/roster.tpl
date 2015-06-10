<div id="roster" ng-controller="RosterController as rosterCtrl">  
    <ul id="rosterlist" class="{if="isset($conf) && $conf.roster == 'show'"}offlineshown{/if} active all">
        <span ng-if="contacts == null" class="nocontacts">
            {$c->__('roster.no_contacts')}
        </span>
        
        <li class="subheader search">{$c->__('roster.results')}</li>
        
            <div id="spinner">
                <img src="{$base_uri}/app/widgets/Roster/img/movim_cloud.svg"/>
            </div>
            
        <div
            ng-if="contacts != null && !group.tombstone"
            ng-repeat="group in contacts"
            id="group{{::group.agroup}}"
            ng-class="{groupshown: rosterCtrl.groupIsShown(group.agroup)}" >
            
            <li class="subheader" ng-click="rosterCtrl.showHideGroup(group.agroup)">
                {{::group.agroup}}
                <span class="info">{{rosterCtrl.getOnlineCount(group.agroupitems)}}/{{group.agroupitems.length}}</span>
            </li>
            <li
                ng-repeat="myjid in group.agroupitems"
                ng-if="!myjid.tombstone"
                id="{{::myjid.ajid}}"
                class="{{myjid.ajiditems.rosterview.inactive}} action {{myjid.ajiditems.rosterview.presencetxt}}"
                ng-attr-title="{{rosterCtrl.getContactTitle(myjid.ajiditems)}}"
                ng-class="{condensed: myjid.ajiditems.status != '' && myjid.ajiditems.status != null }">
                <!--ng-class="rosterCtrl.getContactClient(myjid.ajiditems)"-- >-->
                <div
                    class="action"
                    ng-if="myjid.ajiditems.rosterview.tune != '' || myjid.ajiditems.rosterview.type != '' || myjid.ajiditems.rosterview.subscription != 'both'"
                    ng-switch on="myjid.ajiditems.rosterview.type">
                    <i ng-switch-when="handheld" class="md md-smartphone"></i>
                    <i ng-switch-when="phone" class="md md-smartphone"></i>
                    <i ng-switch-when="web" class="md md-language"></i>
                    <i ng-switch-when="bot" class="md md-memory"></i>
                    <i ng-if="myjid.ajiditems.rosterview.tune" class="md md-play-arrow"></i>
                    <i ng-if="myjid.ajiditems.rosterview.subscription == 'to'" class="md md-call-received"></i>
                    <i ng-if="myjid.ajiditems.rosterview.subscription == 'from'" class="md md-call-made"></i>
                    <i ng-if="myjid.ajiditems.rosterview.subscription == 'none'" class="md md-do-not-disturb"></i>
                </div>

                <span
                    ng-if="::myjid.ajiditems.rosterview.avatar != false"
                    class="icon bubble status {{myjid.ajiditems.rosterview.presencetxt}}"
                    style="background-image: url({{::myjid.ajiditems.rosterview.avatar}})">
                </span>

                <span
                    ng-if="::myjid.ajiditems.rosterview.avatar== false"
                    class="icon bubble status {{myjid.ajiditems.rosterview.presencetxt}} color {{myjid.ajiditems.rosterview.color}}">
                    <i class="md md-person"></i>
                </span>

                <span>{{myjid.ajiditems.rosterview.name}}</span>
                <p ng-if="myjid.ajiditems.status != ''" class="wrap">
                    <span>{{myjid.ajiditems.status}}</span>
                </p>
            </li>
        </div>
    </ul>
    <br />
    <a onclick="Roster_ajaxDisplaySearch()" class="button action color">
        <i class="md md-person-add"></i>
    </a>

</div>
