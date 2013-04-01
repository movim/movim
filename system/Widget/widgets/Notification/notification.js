function removeDiff(params) {
    target = document.getElementById(params[0]);
    setTimeout(function() {target.parentNode.removeChild(target);}, 6000);
}
