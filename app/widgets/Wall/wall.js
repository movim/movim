function hideWall() {
    wall = document.querySelector("#wall");
    wall.parentNode.removeChild(wall);
    createTabs();
}

movim_add_onload(function() {
    MovimMap.init();
    MovimMap.refresh();
});
