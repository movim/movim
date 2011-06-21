function hideNotification(n) {
    n.parentNode.parentNode.removeChild(n.parentNode);
}

function showAlias(n) {
    n.style.display = "none";
    document.querySelector('#notifsvalidate').style.display = "block";
    document.querySelector('#notifsalias').style.display = "block";
}

function getAlias() {
    return document.querySelector('#notifsalias').value;
}
