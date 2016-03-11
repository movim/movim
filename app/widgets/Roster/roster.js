(function(){
    var app = angular.module("roster", []);

    /* Controller for Rosterlist */
    app.controller("RosterController", function($scope){
        /* Cache variables */
        $scope.lsJid = localStorage.getItem("username").replace("@", "at");
        $scope.lsRoster = localStorage.getObject($scope.lsJid + "_Roster") || {};
        $scope.lsGroupState = "groupState" in $scope.lsRoster ? $scope.lsRoster.groupState : {};

        $scope.contacts = [];
        $scope.groups = [];

        /* Dictionaries */
        $scope.lookupgroups = {};
        $scope.lookupjid = {};

        $scope.initContacts = function(list){
            document.getElementById("spinner").style.display = "block";

            /* Sort groups alphabetically */
            list.sort(groupnameCompare);

            $scope.contacts = list;
            /* Populate dictionaries */
            for(var i = 0; i < $scope.contacts.length; i++){
                $scope.lookupgroups[$scope.contacts[i].agroup] = $scope.contacts[i];
                /* Sort jid by presence and alphabetically */
                $scope.contacts[i].agroupitems.sort(jidAvalCompare);

                for(var j = 0; j < $scope.contacts[i].agroupitems.length; j++){
                    $scope.lookupjid[$scope.contacts[i].agroupitems[j].ajid] = $scope.contacts[i].agroupitems[j];
                }
            }
            document.getElementById("spinner").style.display = "none";
            $scope.$apply();
        };

        $scope.initGroups = function(list){
            for(var i in list){
                if(!("rosterGroup_"+i in $scope.lsGroupState)){
                    list[i] = true;
                    $scope.lsGroupState["rosterGroup_" + i] = true;
                }
                else list[i] = $scope.lsGroupState["rosterGroup_" + i];
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
            array.splice(index, 0, element);

            /* Update dictionary from the appropriate index */
            for(var i=index; i<array.length; i++){
                dico[array[i][key]] = array[i];
            }
        };

        $scope.updateContact = function(list){
            if($scope.contacts === null) $scope.contacts = [];
            /* Group change */
            if((list.jid in $scope.lookupjid)
                && !($scope.lookupjid[list.jid].ajiditems.groupname == list.groupname)){
                /* Kill jid from old location or whole group if it's the only jid */
                oldgroupname = $scope.lookupjid[list.jid].ajiditems.groupname;
                if($scope.lookupgroups[oldgroupname].agroupitems.length == 1){
                    $scope.lookupgroups[oldgroupname].tombstone = true;
                    /* Remove group from localStorage */
                    delete $scope.lsGroupState['rosterGroup_'+oldgroupname];
                }
                else{
                    $scope.lookupjid[list.jid].tombstone = true;
                }
            }
            /* New group is not in the list */
            if(!(list.groupname in $scope.lookupgroups)) {
                /* Create group */
                el = {
                    'agroup': list.groupname,
                    'agroupitems': [],
                    'tombstone': false,
                };
                $scope.pushInPlace(el, $scope.contacts, groupnameCompare);
                /* Reference in the localstorage for toggling */
                $scope.lsGroupState["rosterGroup_" + list.groupname] = true;
            }

            /* Jid is in the list and no group change */
            if(list.jid in $scope.lookupjid
                && ($scope.lookupjid[list.jid].ajiditems.groupname == list.groupname))
            {
                $scope.lookupjid[list.jid].aval = list.value;
                $scope.lookupjid[list.jid].atruename = list.rosterview.name;
                $scope.lookupjid[list.jid].ajiditems = list;
                $scope.lookupgroups[list.groupname].agroupitems.sort(jidAvalCompare);
            }
            else{
                el = {
                    'ajid':     list.jid,
                    'atruename':     list.rosterview.name,
                    'aval':     list.value,
                    'ajiditems': list,
                    'tombstone': false,
                };
                $scope.pushInPlace(el, $scope.lookupgroups[list.groupname].agroupitems, jidAvalCompare);
            }
            $scope.$apply();

            //a new li is created, a new listener has to be created...
            document.getElementById(list.jid).onclick = function(){Roster.clickOnContact(this);};
        };

        this.showHideGroup = function(g){
            ls = $scope.lsGroupState["rosterGroup_" + g];
            if(ls === null){
                ls = $scope.lsGroupState.rosterGroup_Ungrouped;
                g = "Ungrouped";
            }

            ls = !ls;

            $scope.lsGroupState["rosterGroup_" + g] = ls;
            $scope.groups[g] = ls;
        };

        this.groupIsShown = function(grp){
            if(typeof $scope.groups[grp] != "undefined"){
                return $scope.groups[grp];
            }
            else return $scope.groups["Ungrouped"];
        };

        this.getContactTitle = function(c){
            title = accentsTidy(c.rosterview.name) + " - " + c.jid;
            if(c.status) title += " - " + c.status;
            return title;
        };

        this.getOnlineCount = function(g){
            count = 0;
            for(var i in g){
                if(g[i].aval < 5) count ++;
            }
            return count;
        };

        this.getContactClient = function(c){
            liclass = "";
            if(c.rosterview.client)
                liclass = "client " + c.rosterview.client;
            return liclass;
        };

        this.getJidStatusResource = function(c){
            lititle = c.jid;
            if(c.status != '') lititle += " - " + c.status;
            lititle += " - " + c.resource;
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
    var lsjid = angular.element(roster).scope().lsJid;

    // Update real localstorage
    angular.element(roster).scope().lsRoster.groupState = angular.element(roster).scope().lsGroupState;
    localStorage.setObject(lsjid + "_Roster", angular.element(roster).scope().lsRoster);
};

/* Functions to call angular inner functions */
function initContacts(tab) {
    tab = JSON.parse(tab);
    if(tab.length == 0) {
        angular.element(roster).scope().contacts = null;
        document.getElementById("spinner").style.display = "none";
    } else
        angular.element(roster).scope().initContacts(tab);
    Roster.refresh();
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


/* === PushInPlace subfunctions === */
function locationOf(element, array, comparer, start, end) {
    if (array.length === 0)
        return 0;
    start = start || 0;
    end = end || array.length;
    var pivot = (start + end) >> 1;  // >>1 = /2
    var c = comparer(element, array[pivot]);
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
    return a.agroup.localeCompare(b.agroup);
};

/* Presence + alphabetical comparison */
var jidAvalCompare = function(a, b) {
    n = a.aval - b.aval;
    if(n == 0){
        n = a.atruename.localeCompare(b.atruename);
    }
    return n ? n < 0 ? -1 : 1 : 0;
};

var Roster = {
    init : function() {
        var search      = document.querySelector('#rostersearch');
        var roster      = document.querySelector('#roster');
        var rosterlist  = document.querySelector('#rosterlist');

        search.oninput = function(event) {
            if(search.value.length > 0) {
                movim_add_class(roster, 'search');
            } else {
                movim_remove_class(roster, 'search');
            }

            // We clear the old search
            var selector_clear = '#rosterlist div > li.found';
            var li = document.querySelectorAll(selector_clear);

            for(i = 0; i < li.length; i++) {
                movim_remove_class(li.item(i), 'found');
            }

            // We select the interesting li
            var selector = '#rosterlist div > li[title*="' + accentsTidy(search.value) + '"]:not(.subheader)';
            li = document.querySelectorAll(selector);
            if(li != null && li.item(0) != null ){
                var g = li.item(0).parentNode.querySelector('.subheader');
                movim_add_class(g, 'found');
                for(i = 0; i < li.length; i++) {
                    if(li.item(i).parentNode.firstChild != g){
                        g = li.item(i).parentNode.querySelector('.subheader');
                        movim_add_class(g, 'found');
                    }
                    movim_add_class(li.item(i), 'found');
                }
            }
        };
    },
    refresh: function() {
        var items = document.querySelectorAll('#rosterlist div > li:not(.subheader)');
        var i = 0;

        while(i < items.length)
        {
            items[i].onclick = function(){Roster.clickOnContact(this);};
            i++;
        }
    },

    reset: function(list) {
        for(i = 0; i < list.length; i++) {
            movim_remove_class(list[i], 'active');
        }
    },

    clearSearch: function() {
        var search = document.querySelector('#rostersearch');
        if(search) {
            search.value = '';
            search.oninput();
        }
    },

    setFound : function(jid) {
        document.querySelector('input[name=searchjid]').value = jid;
    },

    clickOnContact : function(e) {
        Contact_ajaxGetContact(e.id);
        Contact_ajaxRefreshFeed(e.id);
        /*recalculated at each click*/
        var it = document.querySelectorAll('#rosterlist div > li:not(.subheader)');
        Roster.reset(it);
        Roster.clearSearch();
        movim_add_class(e, 'active');
    },
}

MovimWebsocket.attach(function() {
    Roster_ajaxGetRoster();
    Roster.refresh();
    Notification.current('contacts');
});


movim_add_onload(function(){
    Roster.init();
});
