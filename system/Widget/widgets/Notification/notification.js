function removeDiff(params) {
    target = document.getElementById(params[0]);
    console.log(params);
    setTimeout(function() {target.parentNode.removeChild(target.parentNode.firstChild);}, 6000);
}
