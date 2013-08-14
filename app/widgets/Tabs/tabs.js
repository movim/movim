function createTabs() {
    // We search all the div with "tab" class
    var tabs = document.querySelectorAll('.tabelem');
    
    var current = null;
    
    // We create the list
    var html = '';
    for (var i=0; i<tabs.length; i++){
	//if(document.URL.search(tabs[i].id)>-1)
	if(window.location.hash == '#'+tabs[i].id)
	    current = tabs[i].id;
	    
        html += '<li class="' + tabs[i].id + '" onclick="changeTab(this, \'' + tabs[i].id + '\');">';
        html += '    <a href="#" onclick="actDifferent(event);">' + tabs[i].title + '</a>';
        html += '</li>';
    }
	
    
    // We show the first tab
    tabs[0].style.display = "block";

    // We insert the list
    document.querySelector('#navtabs').innerHTML = html;
    
    if(current != null){
	tab = current;	
	menuTab = document.querySelector('li.'+current);	
    }
    
    //if no tab is active, activate the first one
    else {
	tab = document.querySelector('.tabelem').id;	
	menuTab = document.querySelector('li.'+tab);	
    }
    changeTab(menuTab, tab);
}

movim_add_onload(function()
{
    createTabs();
    scroll(0,0);
});

function changeTab(current, n){
    // We grab the tabs list
    var navtabs = document.querySelectorAll('#navtabs li');
    // We clean the class of the li
    for (var j=0; j<navtabs.length; j++) {
        navtabs[j].className = navtabs[j].className.split(" on")[0];
    }
    
    // We add the "on" class to the selected li
    current.className += ' on';
	
    // We hide all the div
    var tabs = document.querySelectorAll('.tabelem');
    for (var i=0; i<tabs.length; i++){
        tabs[i].style.display = 'none';
    }
    // We show the selected div
    var tabOn = document.querySelector('#'+n);
    tabOn.style.display = "block";
    window.location.hash = n;
    
    scroll(0,0);
}

function actDifferent(e){
    e.preventDefault();
    return false;
}	
