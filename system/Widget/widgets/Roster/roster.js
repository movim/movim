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

function incomingPresence(val) {
    target = document.getElementById('roster'+val[0]);
    if(target) {
        target.className = val[1];
    }
    sortRoster();
}

/*ROSTER SEARCH*/
function focusContact(){
	rosterlist = document.querySelector('#rosterlist');
	if(rosterlist.querySelector('.focused') == null){
		//offline are shown
		if(rosterlist.querySelectorAll(".offline").length !=0 && rosterlist.querySelectorAll(".offline").style!=""){
			rosterlist.querySelector("li").className += " focused";
		}
		//offline are hidden
		else{
			rosterlist.querySelector("li:not(.offline)").className += " focused";
		}
		document.querySelector('#right').scrollTop = 0;
	}
	else{
		document.querySelector('#right').scrollTop = rosterlist.querySelector('.focused').offsetTop-document.querySelector('#nav').offsetHeight;
	}
}

function rosterNextGroup(cgp){
	thisGp = gp[0];
	while(cgp != thisGp){
		thisGp = thisGp.nextSibling;
	}
    //here thisGp=cgp
	if(thisGp.nextSibling.nextSibling != null){
        thisGp = thisGp.nextSibling.nextSibling;
        if(rosterInArray(thisGp.querySelector("li"), thisGp.querySelectorAll("li"))){//offline are shown
            currContact = thisGp.querySelector("li");
            console.log(currContact);
        }
        else{
            rosterNext(thisGp.querySelector("li"));
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

function rosterNext(currFocus){
	currGp = currFocus.parentNode;
	viable = false;
	gp = rosterlist.querySelectorAll("div:not([class='chat on'])");
    //querySelectorAll is used to be able to get nextsiblings
	currContact = currGp.querySelectorAll("li")[0];
    //Define contact end limit
    visible = "";
    currentGroupVisible = "";
    if(rosterlist.class != ""){//offline are shown
        visible = rosterlist.querySelectorAll("li");
        currentGroupVisible = currGp.querySelectorAll("li");
    }
    else{
        visible = rosterlist.querySelectorAll("li:not(.offline)");
        currentGroupVisible = currGp.querySelectorAll("li:not(.offline)");
    }
    last = visible[visible.length - 1];
    
    if(currFocus != last){
        while(currContact.className.lastIndexOf("focused") < 0){
            currContact = currContact.nextSibling;
        }
        //here we have currContact = currFocus
        currContact = currContact.nextSibling;
        //here currContact can be null, visible, or invisible
        if(currContact != null){
            //search for the next viable contact or find the end of the group
            while(!rosterInArray(currContact, currentGroupVisible) && currContact != null){
                currContact = currContact.nextSibling;
            }       
            viable = true;
        }
        if(currContact == null || (currContact.nextSibling == null && !viable)){//Change groupe
            if(currGp != gp[gp.length-1] && currContact == null){
                rosterNextGroup(currGp);
            }
            else{//end of roster
                //if(currContact == null)
                currContact = currContact.previousSibling;
            }
        }
        /*elseif(!end){
            //offline are shown
            if(rosterlist.className != ""){
                while(currContact.style.display.lastIndexOf("none") > (-1) && !end){
                    if(currContact.nextSibling != null){
                        currContact = currContact.nextSibling;
                    }
                    else{
                        if(currGp != gp[gp.length-1])
                            rosterNextGroup(currGp);
                        else{
                            end = true;
                        }
                    }
                }
            }
            //offline are hidden
            else{
                if((currContact.className.lastIndexOf("offline") > -1 || currContact.className.lastIndexOf("server") > -1) && !end){
                    console.log(currContact);
                    if(currContact != null){
                        contact = currContact;
                    }
                    else{
                        if(currGp != gp[gp.length-1])
                            rosterNextGroup(currGp);
                        else{
                            end = true;
                        }
                    }
                }
            }
            if(end)
                contact = currFocus;
            else
                contact = currContact;
        }
        else{
            if(end)
                contact = currFocus;
        }*/
    }
    else{
        currContact = currFocus;
    }
    
    giveFocusTo(currContact);
}

/*function rosterPreviousGroup(cgp){
	thisGp = gp[0];
	if(cgp != thisGp){ //not first group
		while(thisGp.nextSibling != cgp){
			thisGp = thisGp.nextSibling;//change group
		}
		console.log(thisGp.querySelectorAll("li:not([style='display: none;'])")[0]);
			while(thisGp.querySelectorAll("li:not([style='display: none;'])").length == 0 && thisGp != firstGroup){
				thisGp = thisGp.previousSibling;
			}
			currGp = thisGp;
			first = currGp.querySelector("li");
			currContact = thisGp.querySelectorAll("li")[thisGp.querySelectorAll("li").length-1];
			console.log(currContact);
	}
}*/

/*function rosterPrevious(currFocus){
	currGp = currFocus.parentNode;
	gp = rosterlist.querySelectorAll("div:not([class='chat on'])");
	firstGroup = gp[0];
	end = false;
	firstContact = currGp.querySelector("li");
    //begin to read at the end of current group
	currContact = currGp.querySelectorAll("li")[currGp.querySelectorAll("li").length -1];
    //defining the upper limit
    if(rosterlist.className != "")//offline are shown
        first = rosterlist.querySelector("li:not([style='display: none;'])");
    else
        first = rosterlist.querySelector("li:not(.offline), li:not([style='display: none;'])");
    if(currFocus != first){
        //til the focused is found, read backward
        while(currContact.className.lastIndexOf("focused") < 0){
            currContact = currContact.previousSibling;
        }
        console.log(currContact);
        currContact = currContact.previousSibling;
        if(currGp.querySelector("li") == currFocus){ //first contact of the group
            rosterPreviousGroup(currGp);
        }
        if(currGp.querySelector("li") != currFocus && !end){
            //offline are shown
            if(rosterlist.querySelector(".offline").style.display != ""){
                while(currContact.style.display.lastIndexOf("none") > (-1)){
                    if(currContact != firstContact){
                        currContact = currContact.previousSibling;
                    }
                    else{
                        rosterPreviousGroup(currGp);
                    }
                }
                if(end)
                    contact = currFocus;
                else
                    contact = currContact;
            }
            //offline are hidden
            else{
                while((currContact.className.lastIndexOf("offline") > -1 || currContact.className.lastIndexOf("server") > -1) && !end){
                    if(currContact != firstContact){
                        currContact = currContact.previousSibling;
                    }
                    else{
                        rosterPreviousGroup(currGp);
                    }
                }
                if(end)
                    contact = currFocus;
                else
                    contact = currContact;
            }
        }
    }
    else{
        contact = currFocus;
    }
}*/
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
	names = rosterlist.getElementsByTagName('span');
	request = document.querySelector('#request').value;
	focused = rosterlist.querySelector('.focused');
    
    //if key pressed is backspace, alphanumeric or delete
	if(e.keyCode==8 || (e.keyCode>47 && e.keyCode<91) || (e.keyCode>95 && e.keyCode<106) || e.keyCode==46){
		focusflag = false;
		for(i = 0; i < parents.length; i++){
            /*hide all contacts that doesn't match*/
			if(names[i].innerHTML.toLowerCase().lastIndexOf(request.toLowerCase()) == -1){
				parents[i].style.display = "none";
			}
            else{
				parents[i].style.display = "list-item";
                /*replace the old focused by the new one if there is an old one*/
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
			contact = rosterlist.querySelector('.focused');
			found = false;
			if(0 == (decallage = contact.offsetHeight))
				decallage = contact.nextSibling.offsetHeight;

			switch(e.keyCode){
				//previous
				case e.keyCode = 38:
					rosterPrevious(rosterlist.querySelector(".focused"));
					if(contact.offsetTop-document.querySelector('#right').scrollTop < decallage){
						document.querySelector('#right').scrollTop = currContact.offsetTop-document.querySelector('#nav').offsetHeight;
					}
					break;
				//next
				case e.keyCode = 40:
					rosterNext(rosterlist.querySelector(".focused"));
					if(contact.offsetTop+decallage-document.querySelector('#right').scrollTop >= document.querySelector('#rostermenu').offsetTop){
						document.querySelector('#right').scrollTop += contact.offsetTop+decallage-document.querySelector('#right').scrollTop - document.querySelector('#rostermenu').offsetTop;
					}
					break;
			}
			/*if(focused.className == (focused.className = focused.className.replace(" focused", "")))
				focused.className = focused.className.replace("focused", "");
			if(contact.className.lastIndexOf("focused")<0){
				contact.className += " focused";
                console.log("303");
            }*/
		}
	}
}

function rosterToggleGroup(h){
    group = document.getElementById(h[0]);
    
    if(group.className == '')
        group.className = 'groupshown';
    else
        group.className = '';
}

function addJid(n) {
    document.querySelector('#addcontact').style.display = "block";
}

function cancelAddJid() {
    document.querySelector('#addcontact').style.display = "none";
}

function getAlias() {
    return document.querySelector('#notifsalias').value;
}

function getAddJid() {
    return document.querySelector('#addjid').value;
}

function getAddAlias() {
    return document.querySelector('#addalias').value;
}
