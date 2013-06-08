function createTabs() {
    // We search all the div with "tab" class
    var tabs = document.querySelectorAll('.tabelem');
    
    // We create the list
    var html =  '<li class="on" onclick="changeTab(this, \'' + tabs[0].id + '\')">' + tabs[0].title +  '</li>';
    for (var i=1; i<tabs.length; i++){
        var html = html + '<li onclick="changeTab(this, \'' + tabs[i].id + '\')">' + tabs[i].title +  '</li>';
        tabs[i].style.display = "none";
    }
    
    // We show the first tab
    tabs[0].style.display = "block";

    // We insert the list
    document.querySelector('#navtabs').innerHTML = html;
}

movim_add_onload(function()
{
    createTabs();
});

function changeTab(a, n) {
    // We grab the tabs list
    var navtabs = document.querySelector('#navtabs');
    // We clean the class of the li
    for (var j=0; j<navtabs.childNodes.length; j++) {
        navtabs.childNodes[j].className = '';
    }
    
    // We add the "on" class to the selected li
    a.className = 'on';
    
    // We hide all the div
    var tabs = document.querySelectorAll('.tabelem');
    for (var i=0; i<tabs.length; i++){
        tabs[i].style.display = "none";
    }
    // We show the selected div
    var tabOn = document.querySelector('#'+n);
    tabOn.style.display = "block"; 
}
