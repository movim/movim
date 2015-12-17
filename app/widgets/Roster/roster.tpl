<div id="roster" ng-controller="RosterController as rosterCtrl">
    <header>
        <ul class="list">
            <li>
                <span id="menu" class="primary on_mobile icon active" onclick="MovimTpl.toggleMenu()"><i class="zmdi zmdi-menu"></i></span>
                <span class="primary on_desktop icon gray"><i class="zmdi zmdi-search"></i></span>
                <form>
                    <div onclick="Roster.init();">
                        <input type="text" name="search" id="rostersearch" autocomplete="off" placeholder="{$c->__('roster.search');}"/>
                    </div>
                </form>
            </li>
        </ul>
    </header>
    <ul id="rosterlist" class="list {if="isset($conf) && isset($conf.roster) && $conf.roster == 'show'"}offlineshown{/if} active all">
        <div ng-if="contacts == null" class="empty placeholder icon contacts">
            <h1>{$c->__('roster.no_contacts_title')}</h1>
            <h4>{$c->__('roster.no_contacts_text')}</h4>
        </div>

        <li class="subheader search">
            <p>{$c->__('roster.results')}</p>
        </li>

        <div id="spinner">
            <img src="{$base_uri}/app/widgets/Roster/img/movim_cloud.svg"/>
        </div>

        <div
            ng-if="contacts != null && !group.tombstone"
            ng-repeat="group in contacts track by group.agroup"
            id="group{{::group.agroup}}"
            ng-class="{groupshown: rosterCtrl.groupIsShown(group.agroup)}" >

            <li class="subheader" ng-click="rosterCtrl.showHideGroup(group.agroup)">
                <p>
                    <span class="info">{{rosterCtrl.getOnlineCount(group.agroupitems)}}/{{group.agroupitems.length}}</span>
                    {{::group.agroup}}
                </p>
            </li>
            <li
                ng-repeat="myjid in group.agroupitems track by myjid.ajid"
                ng-if="!myjid.tombstone"
                id="{{::myjid.ajid}}"
                class="{{myjid.ajiditems.rosterview.inactive}} action {{myjid.ajiditems.rosterview.presencetxt}}"
                ng-attr-title="{{rosterCtrl.getContactTitle(myjid.ajiditems)}}"
                ng-class="{condensed: myjid.ajiditems.status != '' && myjid.ajiditems.status != null }">
                <span
                    ng-if="::myjid.ajiditems.rosterview.avatar != false"
                    class="primary icon bubble status {{myjid.ajiditems.rosterview.presencetxt}}"
                    style="background-image: url({{::myjid.ajiditems.rosterview.avatar}})">
                </span>

                <span
                    ng-if="::myjid.ajiditems.rosterview.avatar== false"
                    class="primary icon bubble status {{myjid.ajiditems.rosterview.presencetxt}} color {{myjid.ajiditems.rosterview.color}}">
                    <i class="zmdi zmdi-account"></i>
                </span>

                <span
                    class="control icon gray"
                    ng-if="myjid.ajiditems.rosterview.tune || myjid.ajiditems.rosterview.type != '' || myjid.ajiditems.rosterview.subscription != 'both'"
                    ng-switch on="myjid.ajiditems.rosterview.type">
                    <i ng-switch-when="handheld" class="zmdi zmdi-smartphone"></i>
                    <i ng-switch-when="phone" class="zmdi zmdi-smartphone"></i>
                    <i ng-switch-when="web" class="zmdi zmdi-globe-alt"></i>
                    <i ng-switch-when="bot" class="zmdi zmdi-memory"></i>
                    <i ng-if="myjid.ajiditems.rosterview.tune" class="zmdi zmdi-play"></i>
                    <i ng-if="myjid.ajiditems.rosterview.subscription == 'to'" class="zmdi zmdi-arrow-in"></i>
                    <i ng-if="myjid.ajiditems.rosterview.subscription == 'from'" class="zmdi zmdi-arrow-out"></i>
                    <i ng-if="myjid.ajiditems.rosterview.subscription == 'none'" class="zmdi zmdi-block"></i>
                </span>


                <p class="normal">{{myjid.ajiditems.rosterview.name}}</p>
                <p ng-if="myjid.ajiditems.status && myjid.ajiditems.status != ''">
                    {{myjid.ajiditems.status}}
                </p>
            </li>
        </div>
    </ul>
    <br />
    <a onclick="Roster_ajaxDisplaySearch()" class="button action color">
        <i class="zmdi zmdi-account-add"></i>
    </a>

</div>
