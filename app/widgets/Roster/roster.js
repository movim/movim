function sortRoster() {

    roster = document.querySelector('#rosterlist');
    contacts = roster.querySelectorAll('li');

    online = roster.querySelectorAll('.online');
    for(i = 0; i < online.length; i++) {
        online.item(i).parentNode.insertBefore(online.item(i), contacts.item(contacts.length))
    }
    away = roster.querySelectorAll('.away');
    for(i = 0; i < away.length; i++) {
        away.item(i).parentNode.insertBefore(away.item(i), contacts.item(contacts.length))
    }
    dnd = roster.querySelectorAll('.dnd');
    for(i = 0; i < dnd.length; i++) {
        dnd.item(i).parentNode.insertBefore(dnd.item(i), contacts.item(contacts.length))
    }
    xa = roster.querySelectorAll('.xa');
    for(i = 0; i < xa.length; i++) {
        xa.item(i).parentNode.insertBefore(xa.item(i), contacts.item(contacts.length))
    }
    offline = roster.querySelectorAll('.offline');
    for(i = 0; i < offline.length; i++) {
        offline.item(i).parentNode.insertBefore(offline.item(i), contacts.item(contacts.length))
    }

    server_error = roster.querySelectorAll('.server_error');
    for(i = 0; i < server_error.length; i++) {
        server_error.item(i).parentNode.insertBefore(server_error.item(i), contacts.item(contacts.length))
    }
}

function showRoster(boolOffline) {
    if(boolOffline == '1')
        document.querySelector('ul#rosterlist').className = 'offlineshown';
    else
        document.querySelector('ul#rosterlist').className = '';
}

function showHideRoster(hide) {
    if(hide == '1')
        document.querySelector('#roster').className = 'hide';
    else
        document.querySelector('#roster').className = '';        
}

function incomingPresence(val) {
    target = document.getElementById('roster'+val[0]);
    if(target) {
        target.className = val[1];
    }
    sortRoster();
}

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
/*ROSTER SEARCH*/
/*
function focusContact(){
	rosterlist = document.querySelector('#rosterlist');
	focused = rosterlist.querySelector('.focused');
	if( focused != null){
		focused.className = focused.className.split(' ')[0];
	}
	
    allLi = "";
	//offline are shown
	if(rosterlist.className!=""){
        allLi = rosterlist.querySelectorAll("li");
	}
	//offline are hidden
	else{
		allLi = rosterlist.querySelectorAll("li:not(.offline)");
	}
    for(j=0; j<allLi.length; j++){
        if(rosterInArray(allLi[j], allLi)){
            allLi[j].className += " focused";
            break;
        }
    }
	document.querySelector('#right').scrollTop = 0;
}

function rosterNextGroup(cgp){
	if(cgp.nextSibling.nextSibling != null){
        cgp = cgp.nextSibling.nextSibling;
        while(cgp.className == "" && cgp != gp[gp.length-1]){ //do not read hidden groups
            rosterNextGroup(cgp);
        }
        newLis = cgp.querySelectorAll("li");
        if(rosterInArray(newLis[0], newLis)){
            returned = newLis[0];
            decalage += cgp.querySelector("h1").offsetHeight;
        }
        else{
            rosterNext(newLis[0]);
        }
	}
}


function rosterNext(currFocus){
	currGp = currFocus.parentNode;
	viable = false;
	gp = rosterlist.querySelectorAll("div:not(.chat)");
    returned = "";
    //Define contact end limit
    visible = "";
    currentGroupVisible = "";
    
    if(rosterlist.className != ""){//offline are shown
        visible = rosterlist.querySelectorAll("li");
        currentGroupVisible = currGp.querySelectorAll("li");
    }
    else{
        visible = rosterlist.querySelectorAll("li:not(.offline)");
        currentGroupVisible = currGp.querySelectorAll("li:not(.offline)");
    }
    last = visible[visible.length - 1];
    
    if(currFocus != last){
        //search for currFocused or end of group
        if(currFocus.nextSibling != null){ //not end of group
            currFocus = currFocus.nextSibling;
            //search for the next viable contact or find the end of the group
            while(!rosterInArray(currFocus, currentGroupVisible) && currFocus.nextSibling != null){
                currFocus = currFocus.nextSibling;
            }    
            if(rosterInArray(currFocus, currentGroupVisible)){    
                viable = true;
                returned = currFocus;
            }
        }
        if(currFocus.nextSibling == null && !viable){//Change groupe
            if(currGp != gp[gp.length-1]){
                rosterNextGroup(currGp);
            }
        }
    }else{returned = currFocus;}    
    
    giveFocusTo(returned);
}

function rosterPreviousGroup(cgp){
    if(cgp.previousSibling.previousSibling != null){
        cgp = cgp.previousSibling.previousSibling;
        while(cgp.className == "" && cgp != gp[0]){ //do not read hidden groups
            rosterPreviousGroup(cgp);
        }
        newLis = cgp.querySelectorAll("li");
        newLi = newLis[newLis.length - 1];
        if(rosterInArray(newLi, newLis)){
            returned = newLi;
            decalage -= cgp.querySelector("h1").offsetHeight;
        }
        else{
            rosterPrevious(newLi);
        }
	}
}

function rosterPrevious(currFocus){
    currGp = currFocus.parentNode;
	viable = false;
	gp = rosterlist.querySelectorAll("div:not([class='chat on'])");
    returned = "";
    visible = "";
    currentGroupVisible = "";
    
    //Define contact begin limit
    if(rosterlist.class != ""){//offline are shown
        visible = rosterlist.querySelectorAll("li");
        currentGroupVisible = currGp.querySelectorAll("li");
    }
    else{
        visible = rosterlist.querySelectorAll("li:not(.offline)");
        currentGroupVisible = currGp.querySelectorAll("li:not(.offline)");
    }
    first = visible[0];
    
    if(currFocus != first){
        //currentFocus' previous can be null, not li or viable
        if(currFocus.previousSibling != null){ //not beginning of group
            currFocus = currFocus.previousSibling;
            //search for the next viable contact or find the end of the group
            while(!rosterInArray(currFocus, currentGroupVisible) && currFocus.previousSibling != null){
                currFocus = currFocus.previousSibling;
            }    
            if(rosterInArray(currFocus, currentGroupVisible)){    
                viable = true;
                returned = currFocus;
            }
        }
        if(currFocus.previousSibling == null && !viable){//Change groupe
            if(currGp != gp[0]){
                rosterPreviousGroup(currGp);
            }
        }
    }else{returned = currFocus;}    
    
    giveFocusTo(returned);
}
function giveFocusTo(newFocused){
    focused = document.querySelector('#rosterlist').querySelector('.focused');
    if(newFocused != focused){
        focused.className = focused.className.split(' ')[0];
        newFocused.className += " focused";
    }
}

function rosterSearch(e){
	rosterlist = document.querySelector('#rosterlist');
	parents = rosterlist.querySelectorAll('li');
	names = rosterlist.getElementsByTagName('a');
	request = document.querySelector('#request').value;
	focused = rosterlist.querySelector('.focused');
    
    //if key pressed is backspace, alphanumeric or delete
	if(e.keyCode==8 || (e.keyCode>47 && e.keyCode<91) || (e.keyCode>95 && e.keyCode<106) || e.keyCode==46){
		focusflag = false;
		for(i = 0; i < parents.length; i++){
            // hide all contacts that doesn't match
			if(names[i].innerHTML.toLowerCase().lastIndexOf(request.toLowerCase()) == -1){
				parents[i].style.display = "none";
			}
            else{
				parents[i].style.display = "list-item";
                // replace the old focused by the new one if there is an old one
				if(!focusflag){
					giveFocusTo(parents[i]);
					focusflag = true;
				}
			}
		}
		document.querySelector('#right').scrollTop = 0;
	}
	else{
		if(e.keyCode == 13){ //key pressed is enter; launch chat
			eval(focused.getElementsByTagName("div")[0].getAttribute("onclick"));
		}
		if(e.keyCode>36 && e.keyCode<41){ //key pressed is an arrow
			//begin reading from focused
            decalage = focused.offsetHeight;
			//if(0 == (decalage = focused.offsetHeight))
				//decalage = focused.nextSibling.offsetHeight;
			switch(e.keyCode){
				//previous
				case e.keyCode = 38:
					rosterPrevious(rosterlist.querySelector(".focused"));
                    //top of the focused must be under navbar
                    diff = focused.offsetTop - rosterlist.offsetTop - document.querySelector('#right').scrollTop - focused.offsetHeight;
                    
                    if(decalage < focused.offsetHeight)
                            diff -= rosterlist.querySelector("h1").offsetHeight;
					if(diff < 0){
						document.querySelector('#right').scrollTop += diff;
					}
					break;
				//next
				case e.keyCode = 40:
					rosterNext(rosterlist.querySelector(".focused"));                    
                    //bottom of the focused must be over the rostermenu, scrollTop is the only variable
                    diff = focused.offsetTop + rosterlist.offsetTop + focused.offsetHeight - document.querySelector('#rostermenu').offsetTop - document.querySelector('#right').scrollTop;
                    if(decalage > focused.offsetHeight)
                            diff += rosterlist.querySelector("h1").offsetHeight;
					if(diff > 0){
                        document.querySelector('#right').scrollTop += diff;
					}
					break;
			}
		}
	}
}

function rosterInArray(thing, array){
    for(i=0; i<array.length; i++){
        if(array[i] == thing){
            visibility = thing.currentStyle ? thing.currentStyle.display :
                              getComputedStyle(thing, null).display;
            
            if(visibility != "none"){
                return true;
            }
        }
    }
    return false;
}
*/
function rosterToggleGroup(h){
    group = document.getElementById(h[0]);
    
    if(group.className == '')
        group.className = 'groupshown';
    else
        group.className = '';
}

