(function(){
    var app = angular.module("roster", []);

    app.controller("RosterMenuController", function(){
        this.checkoutAddJid = function(event){
            if(event.key == "Enter")
                Roster_ajaxSearchContact(event.target.value);
        };
    });

    app.controller("RosterController", function($scope){
        $scope.contacts = /*localStorage.getObject('rosterContacts') ||*/ [];
        $scope.groups = /*localStorage.getObject('rosterGroups') ||*/ [];

        /* Dictionaries */
        $scope.lookupgroups = {};
        $scope.lookupjid = {};
        $scope.lookupressource = {};
        
        $scope.initContacts = function(list){
            //$scope.contacts = [];
            for(i=0; i<list.length; i++){
                /* New group */
                if(!(list[i].groupname in $scope.lookupgroups)){
                    l = $scope.contacts.length;
                    $scope.contacts.push({
                        'agroup': list[i].groupname, 
                        'agroupitems': []
                    });
                    $scope.lookupgroups[list[i].groupname] = $scope.contacts[l];
                }
                /* New jid */
                if(!(list[i].jid in $scope.lookupjid)){
                    l = $scope.lookupgroups[list[i].groupname].agroupitems.length;
                    $scope.lookupgroups[list[i].groupname].agroupitems.push({
                        'ajid':     list[i].jid, 
                        'aval':     list[i].value,
                        'ajiditems': []
                    });
                    $scope.lookupjid[list[i].jid] = $scope.lookupgroups[list[i].groupname].agroupitems[l];
                }
                /* New ressource */
                if(!(list[i].jid+"/"+list[i].ressource in $scope.lookupressource)){
                    l = $scope.lookupjid[list[i].jid].ajiditems.length;
                    $scope.lookupjid[list[i].jid].ajiditems.push(list[i]);
                    $scope.lookupressource[list[i].jid + "/" + list[i].ressource] = $scope.lookupjid[list[i].jid].ajiditems[l];
                }
            }
            /* Sort jid by presence in each group and update jid dictionary */
            for(i=0; i<$scope.contacts.length; i++){
                $scope.contacts[i].agroupitems.sort(function(a, b){return a.aval - b.aval;});
                for(j=0; j<$scope.contacts[i].agroupitems.length; j++){
                    jid = $scope.contacts[i].agroupitems[j].jid;
                    $scope.lookupjid[jid] = $scope.contacts[i].agroupitems[j];
                }
            }
            
            localStorage.setObject('rosterContacts', $scope.contacts);
            $scope.$apply();
        };

        $scope.initGroups = function(list){
            //$scope.groups = [];
            for (i in list){
                if(localStorage.getItem("rosterGroup_"+i) == null){
                    list[i] = true;
                    localStorage.setItem("rosterGroup_"+i, true);
                }
                else list[i] = localStorage.getItem("rosterGroup_"+i);
            }
            $scope.groups = list;
            localStorage.setObject('rosterGroups', $scope.groups);
            $scope.$apply();
        };

        $scope.updatePresence = function(list){
            /* New group */
            if(!(list[0].groupname in $scope.lookupgroups)){
                l = $scope.contacts.length;
                $scope.contacts.push({
                    'agroup': list[0].groupname,
                    'agroupitems': []
                });
                $scope.lookupgroups[list[0].groupname] = $scope.contacts[l];
            }
            /* New jid */
            if(!(list[0].jid in $scope.lookupjid)){
                l = $scope.lookupgroups[list[0].groupname].agroupitems.length;
                $scope.lookupgroups[list[0].groupname].agroupitems.push({
                    'ajid':     list[0].jid,
                    'aval':     list[0].value,
                    'ajiditems': []
                });
                $scope.lookupjid[list[0].jid] = $scope.lookupgroups[list[0].groupname].agroupitems[l];
            }

            /* Replace the ajiditems by the new list of ressource */
            $scope.lookupjid[list[0].jid].ajiditems = list;
            /* Update the value of the global presence */
            $scope.lookupjid[list[0].jid].aval = list[0].value;
            /* Update the ressources dictionary */
            for(i=0; i<list.length; i++){
                resid = list[i].jid + "/" + list[i].ressource;
                $scope.lookupressource[resid] = $scope.lookupjid[list[0].jid].ajiditems[i];
            }

             /*
             * Sort jid array of the concerned group by global presence of each jid
             * and update jids dictionary
             **/
            $scope.lookupgroups[list[0].groupname].agroupitems.sort(function(a, b){return a.aval - b.aval;});
            for(j=0; j<$scope.lookupgroups[list[0].groupname].agroupitems.length; j++){
                jid = $scope.lookupgroups[list[0].groupname].agroupitems[j].jid;
                $scope.lookupjid[jid] = $scope.lookupgroups[list[0].groupname].agroupitems[j];
            }

            $scope.$apply();
        };

        this.showHideGroup = function(g){
            ls = localStorage.getItem("rosterGroup_"+g);
            if(ls == null){
                ls = localStorage.getItem("rosterGroup_ungrouped");
                g = "ungrouped";
            }

            ls = ls == 'true' ? 'false' : 'true';

            localStorage.setItem("rosterGroup_"+g, ls);
            $scope.groups[g] = ls;
        };

        this.postChatAction = function(c){
            eval(c.rosterview.openchat);
        };
        
        this.postJingleAction = function(c){
            Popup.close(); 
            Popup.open(c.jid+"/"+c.ressource);
        };

        this.groupIsShown = function(grp){
            if(typeof $scope.groups[grp] != "undefined"){
                return $scope.groups[grp];
            }
            else return $scope.groups["ungrouped"];
        };

        this.offlineIsShown = function(){
            if(localStorage.getItem("rosterShow_offline") == "true")
                return "offlineshown";
            else
                return "";
        };

        this.getContactTitle = function(c){
            status = c.status || "";
            ressource = c.ressource || "";
            title = c.jid;
            if(status != "") title += " - " + status;
            if(ressource != "") title += " - " + ressource;
            return title;
        };

        this.getContactClient = function(c){
            liclass = "";
            if(c.rosterview.client)
                liclass = "client "+c.rosterview.client;
            return liclass;
        };

        this.getContactClient = function(c){
            liclass = "";
            if(c.rosterview.client)
                liclass = "client "+c.rosterview.client;
            return liclass;
        };
        
        this.getJidStatusRessource = function(c){
            lititle = c.jid;
            if(c.status != '') lititle+= " - "+c.status;
            lititle += " - "+c.ressource;
            return lititle;
        };
        
        this.getPresenceInactiveClient = function(c){
            liclass = c.rosterview.presencetxt+" "+contact.rosterview.inactive;
            if(c.client) liclass += " client "+c.client;
            return liclass;
        };
    });
})();

function initContacts(tab){
    angular.element(roster).scope().initContacts(JSON.parse(tab));
}

function initGroups(tab){
    angular.element(roster).scope().initGroups(JSON.parse(tab));
}

function updatePresence(tab){
    angular.element(roster).scope().updatePresence(JSON.parse(tab));
}

function showHideOffline() {
    if(localStorage.getItem("rosterShow_offline") != "true" ){
        document.querySelector('ul#rosterlist').className = 'offlineshown';
        localStorage.setItem("rosterShow_offline", "true");
    }
    else{
        document.querySelector('ul#rosterlist').className = '';
        localStorage.setItem("rosterShow_offline", "false");
    }
}

MovimWebsocket.attach(function()
{
    Roster_ajaxGetRoster();
});

movim_add_onload(function()
{    
    var search      = document.querySelector('#rostersearch');
    var roster      = document.querySelector('#roster');
    var rosterlist  = document.querySelector('#rosterlist');
    
    var roster_classback      = document.querySelector('#roster').className;
    var rosterlist_classback  = document.querySelector('#rosterlist').className;

    roster.onblur  = function() {
        roster.className = roster_classback;
        rosterlist.className = rosterlist_classback;
    };
    search.onkeyup = function(event) {
        if(search.value.length > 0) {
            roster.className = 'search';
            rosterlist.className = 'offlineshown';
        } else {
            roster.className = roster_classback;
            rosterlist.className = rosterlist_classback;
        }

        // We clear the old search
        var selector_clear = '#rosterlist div > li';
        var li = document.querySelectorAll(selector_clear);

        for(i = 0; i < li.length; i++) {
            li.item(i).className = '';
        }

        // We select the interesting li
        var selector = '#rosterlist div > li[title*=\'' + search.value + '\']';
        var li = document.querySelectorAll(selector);

        for(i = 0; i < li.length; i++) {
            li.item(i).className = 'found';
        }
    };
});
