function createTabs() {
    // We search all the div with "tab" class
    var tabs = document.querySelectorAll('.tabelem');
    
    // We create the list
    var html = '';
    for (var i=0; i<tabs.length; i++){
		addclass = '';
		if(document.URL.search(tabs[i].id)>-1)
			addclass = 'class="on"';
        html += '<li ' + addclass + ' onclick="changeTab(this, \'' + tabs[i].id + '\');"> <a href="#'+tabs[i].id+'">' + tabs[i].title + '</a></li>';
    }
	
    
    // We show the first tab
    tabs[0].style.display = "block";

    // We insert the list
    document.querySelector('#navtabs').innerHTML = html;
	
	//if no tab is active, activate the first one
	if(document.querySelector(".on") == null){
		start = document.querySelector('.tabelem').id;		
		window.location = "#"+start;
		document.querySelector('#navtabs li').className = 'on';
	}
}

movim_add_onload(function()
{
    createTabs();
});

function changeTab(current, n){
    // We grab the tabs list
    var navtabs = document.querySelectorAll('#navtabs li');
    // We clean the class of the li
    for (var j=0; j<navtabs.length; j++) {
        navtabs[j].className = '';
    }
    
    // We add the "on" class to the selected li
    current.className = 'on';
	
	// We hide all the div
    var tabs = document.querySelectorAll('.tabelem');
    for (var i=0; i<tabs.length; i++){
        tabs[i].style.display = "none";
    }
    // We show the selected div
    var tabOn = document.querySelector('#'+n);
    tabOn.style.display = "block";
}
