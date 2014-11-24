function hidePubsubSubscription() {
    wall = document.querySelector("#groupsubscribedlistfromfriend");
    wall.parentNode.removeChild(wall);
    createTabs();
}
