<div id="roster" ng-controller="RosterController as rosterCtrl">  
    <ul id="rosterlist" class="offlineshown active all">
        <span ng-if="contacts == null" class="nocontacts">
            {$c->__('roster.no_contacts')}
            <br />
            <br />
            <a class="button color green" href="{$c->route('explore')}">
                <i class="fa fa-compass"></i> {$c->__('page.explore')}
            </a>
        </span>
        
        <li class="subheader search">{$c->__('roster.results')}</li>
        <div
            ng-if="contacts != null && !group.tombstone"
            ng-repeat="group in contacts"
            id="group{{::group.agroup}}"
            ng-class="{groupshown: rosterCtrl.groupIsShown(group.agroup)}" >
            <li class="subheader" ng-click="rosterCtrl.showHideGroup(group.agroup)">
                {{::group.agroup}}
            </li>
            <li
                ng-repeat="myjid in group.agroupitems"
                ng-if="!myjid.tombstone"
                id="{{::myjid.ajid}}"
                class="{{myjid.ajiditems.rosterview.inactive}} action"
                ng-attr-title="{{rosterCtrl.getContactTitle(myjid.ajiditems)}}"
                ng-class="{condensed: myjid.ajiditems.status != '' && myjid.ajiditems.status != null }"
                ng-class="rosterCtrl.getContactClient(myjid.ajiditems)" >
                <div
                    class="action"
                    ng-if="myjid.ajiditems.rosterview.tune != '' || myjid.ajiditems.rosterview.type != '' "
                    ng-switch on="myjid.ajiditems.rosterview.type">
                    <i ng-switch-when="handheld" class="md md-smartphone"></i>
                    <i ng-switch-when="web" class="md md-language"></i>
                    <i ng-switch-when="bot" class="md md-memory"></i>
                    <i ng-if="myjid.ajiditems.rosterview.tune" class="md md-play-arrow"></i>
                </div>

                <span class="icon bubble status {{myjid.ajiditems.rosterview.presencetxt}}">
                    <img
                        class="avatar"
                        ng-src="{{::myjid.ajiditems.rosterview.avatar}}"
                        alt="avatar"
                    />
                </span>
                <!--<div class="chat on"></div>-->
                {{myjid.ajiditems.rosterview.name}}
                <p ng-if="myjid.ajiditems.status != ''" class="wrap">
                    <span>{{myjid.ajiditems.status}}</span>
                </p>
            </li>
        </div>
    </ul>
</div>
