function hideWall() {
    wall = document.querySelector("#wall");
    wall.parentNode.removeChild(wall);
    createTabs();
}
