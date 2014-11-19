(function(){
    var app = angular.module("roster", []);

    /* Controller for Rostermenu */
    app.controller("RosterMenuController", function(){
        this.checkoutAddJid = function(event){
            if(event.key == "Enter")
                Roster_ajaxSearchContact(event.target.value);
        };
    });

    /* Controller for Rosterlist */
    app.controller("RosterController", function($scope){
        $scope.contacts = localStorage.getObject('rosterContacts') || [];
        $scope.groups = [];
        
        /* Dictionaries */
        $scope.lookupgroups = {};
        $scope.lookupjid = {};

        $scope.initContacts = function(list){
            if($scope.contacts.length == 0){
                for(var i = 0; i < list.length; i++){
                    /* New group */
                    if(!(list[i].groupname in $scope.lookupgroups)){
                        el = {
                            'agroup': list[i].groupname,
                            'agroupitems': [],
                            'tombstone': false,
                        };
                        $scope.pushInPlace(el, $scope.contacts, groupnameCompare);
                        
                        /* Create a reference in the localstorage for toggling */
                        localStorage.setItem("rosterGroup_"+list[0].groupname, true);
                    }
                    /* New jid */
                    if(!(list[i].jid in $scope.lookupjid)){
                        el = {
                            'ajid': list[i].jid,
                            'aval': list[i].value,
                            'ajiditems': [],
                            'tombstone': false,
                        };
                        $scope.pushInPlace(el, $scope.lookupgroups[list[i].groupname].agroupitems, jidAvalCompare);
                    }
                    /* New ressource (can't just push the whole set of same jid because there is no set) */
                    if(!$scope.isInJidItems(list[i].jid, list[i].ressource)){
                        $scope.pushInPlace(list[i], $scope.lookupjid[list[i].jid].ajiditems, ressourceCompare);
                    }
                }
            }
            /* Rebound from cache */
            else{
                for(var i = 0; i < $scope.contacts.length; i++){
                    if(!$scope.contacts[i].tombstone){
                        $scope.lookupgroups[$scope.contacts[i].agroup] = $scope.contacts[i];
                        for(var j = 0; j < $scope.contacts[i].agroupitems.length; j++){
                            if(!$scope.contacts[i].agroupitems[j].tombstone)
                                $scope.lookupjid[$scope.contacts[i].agroupitems[j].ajid] = $scope.contacts[i].agroupitems[j];
                            else
                                $scope.contacts[i].agroupitems.splice(j, 1);                        }
                    }
                    else
                        $scope.contacts.splice(i, 1);
                }
            }
            
            $scope.$apply();
        };
        
        /* $scope.isInGroupItems is only for debugging purpose */
        $scope.isInGroupItems = function(group, jid){
            l = $scope.lookupgroups[group].agroupitems.length;
            for(var i = 0; i < l; i++){
                if($scope.lookupgroups[group].agroupitems[i].ajid == jid)
                    return i;
            }
            return false;
        };
        
        $scope.isInJidItems = function(jid, ressource){
            l = $scope.lookupjid[jid].ajiditems.length;
            for(var i = 0; i < l; i++){
                if($scope.lookupjid[jid].ajiditems[i].ressource == ressource)
                    return true;
            }
            return false;
        };
        
        $scope.initGroups = function(list){
            for (var i in list){
                if(localStorage.getItem("rosterGroup_"+i) == null){
                    list[i] = true;
                    localStorage.setItem("rosterGroup_"+i, true);
                }
                else list[i] = localStorage.getItem("rosterGroup_"+i);
            }
            $scope.groups = list;
            
            $scope.$apply();
        };

        $scope.deleteContact = function(jid){
            $scope.lookupjid[jid].tombstone = true;
            
            $scope.$apply();
        };
        
        $scope.pushInPlace = function(element, array, comparer){
            if(array === $scope.contacts){
                dico = $scope.lookupgroups;
                key = "agroup";
            } else {
                dico = $scope.lookupjid;
                key = "ajid";
            }
            
            /* Put element in the right place inside array */
            index = locationOf(element, array, comparer); 
            //if(comparer == groupnameCompare){
            array.splice(index, 0, element);
            
            /* Update dictionnary from the appropriate index */
            for(var i=index; i<array.length; i++){
                dico[array[i][key]] = array[i];
                //if(comparer == groupnameCompare){
            }
        };

        $scope.updateContact = function(list){
            console.log(list);
            if($scope.contacts === null) $scope.contacts = [];
            /* Group change */
            if((list[0].jid in $scope.lookupjid) 
                && !($scope.lookupjid[list[0].jid].ajiditems[0].groupname == list[0].groupname)){
                /* Kill jid from old location or whole group if it's the only jid */
                oldgroupname = $scope.lookupjid[list[0].jid].ajiditems[0].groupname;
                if($scope.lookupgroups[oldgroupname].agroupitems.length == 1)
                    $scope.lookupgroups[oldgroupname].tombstone = true;
                else
                    $scope.lookupjid[list[0].jid].tombstone = true;
                
                console.log("Removed jid from oldgroup : "+oldgroupname+"["+$scope.lookupgroups[oldgroupname].agroupitems.length+"] => "+list[0].groupname);
            }
            /* New group is not in the list */
            if(!(list[0].groupname in $scope.lookupgroups)) {
                console.log("Creation of new group: "+list[0].groupname);
                /* Create group */
                el = {
                    'agroup': list[0].groupname,
                    'agroupitems': [],
                    'tombstone': false,
                };
                $scope.pushInPlace(el, $scope.contacts, groupnameCompare);
                /* Reference in the localstorage for toggling */
                localStorage.setItem("rosterGroup_"+list[0].groupname, true);
            }
            /* New Group has been killed before */
            /*else if($scope.lookupgroups[list[0].groupname].tombstone) {
                console.log("Tombstone is true for "+list[0].groupname);
                $scope.lookupgroups[list[0].groupname].tombstone = false;
                $scope.lookupgroups[list[0].groupname].agroupitems = [];
            }*/
                
            /* Jid is in the list and no group change */
            if(list[0].jid in $scope.lookupjid 
                && ($scope.lookupjid[list[0].jid].ajiditems[0].groupname == list[0].groupname)){
                console.log("Change value of "+list[0].jid+" in "+list[0].groupname+" group.");
                //$scope.lookupgroups[list[0].groupname].agroupitems[gi].ajiditems = list
                //var gi = $scope.isInGroupItems(list[0].groupname, list[0].jid);
                $scope.lookupjid[list[0].jid].aval = list[0].value;
                $scope.lookupjid[list[0].jid].ajiditems = list;
                $scope.lookupgroups[list[0].groupname].agroupitems.sort(jidAvalCompare);
            }
            else{
                console.log("Push "+list[0].jid+"("+list[0].value+") in "+list[0].groupname+" group.");
                el = {
                    'ajid':     list[0].jid,
                    'aval':     list[0].value,
                    'ajiditems': list,
                    'tombstone': false,
                };
                $scope.pushInPlace(el, $scope.lookupgroups[list[0].groupname].agroupitems, jidAvalCompare);
            }
            $scope.$apply();
        };

        this.showHideGroup = function(g){
            ls = localStorage.getItem("rosterGroup_"+g);
            if(ls == null){
                ls = localStorage.getItem("rosterGroup_Ungrouped");
                g = "Ungrouped";
            }

            ls = (ls == 'true' || ls == true) ? 'false' : 'true';

            localStorage.setItem("rosterGroup_"+g, ls);
            $scope.groups[g] = ls;
        };

        this.postChatAction = function(c){
            eval(c.rosterview.openchat);
        };
        
        this.postJingleAction = function(c){
            Popup.close(); 
            Popup.open(c.jid + "/" + c.ressource);
        };

        this.groupIsShown = function(grp){
            if(typeof $scope.groups[grp] != "undefined"){
                return $scope.groups[grp];
            }
            else return $scope.groups["Ungrouped"];
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
                liclass = "client " + c.rosterview.client;
            return liclass;
        };
        
        this.getJidStatusRessource = function(c){
            lititle = c.jid;
            if(c.status != '') lititle += " - " + c.status;
            lititle += " - " + c.ressource;
            return lititle;
        };
        
        this.getPresenceInactiveClient = function(c){
            liclass = c.rosterview.presencetxt + " " + contact.rosterview.inactive;
            if(c.client) liclass += " client " + c.client;
            return liclass;
        };
    });
})();

window.onunload = window.onbeforeunload = function(e){
    localStorage.setObject('rosterContacts', angular.element(roster).scope().contacts);
};


/* Functions to call angular inner functions */
function initContacts(tab){
    if(tab.length == 0)
        angular.element(roster).scope().contacts = null;
    else /*if(localStorage.getObject("rosterContacts") === null)*/{
        angular.element(roster).scope().initContacts(JSON.parse(tab));
    }
}

function initGroups(tab){
    angular.element(roster).scope().initGroups(JSON.parse(tab));
}

function updateContact(tab){
    angular.element(roster).scope().updateContact(JSON.parse(tab));
}

function deleteContact(jid){
    angular.element(roster).scope().deleteContact(jid);
}

function locationOf(element, array, comparer, start, end) {
    if (array.length === 0)
        return 0;
    start = start || 0;
    end = end || array.length;
    var pivot = (start + end) >> 1;  // >>1 = /2
    var c = comparer(element, array[pivot]);
    //if(comparer == groupnameCompare){
    if ((end - start) <= 1){
        return (c == -1) ? pivot : pivot+1;
    }
    
    switch (c) {
        case -1: return locationOf(element, array, comparer, start, pivot);
        case 0: return pivot;
        case 1: return locationOf(element, array, comparer, pivot, end);
    };
};

/* Object comparison functions */
var groupnameCompare = function(a, b) {
    //console.log(a.agroup+" vs "+b.agroup);
    return a.agroup.localeCompare(b.agroup);
};

var ressourceCompare = function(a, b) {
    n = a.value - b.value;
    //console.log(a.value+" vs "+b.value);
    return n ? n < 0 ? -1 : 1 : 0;
};

var jidCompare = function(a, b) {
    //console.log(a.jid+" vs "+b.ajid);
    return a.jid.localeCompare(b.ajid);
};

var jidAvalCompare = function(a, b) {
    n = a.aval - b.aval;
    return n ? n < 0 ? -1 : 1 : 0;
};

/* === Old functions still in use === */
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
