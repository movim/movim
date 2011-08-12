function hideNotification(n) {
    n.parentNode.parentNode.removeChild(n.parentNode);
}

function showAlias(n) {
    n.style.display = "none";
    document.querySelector('#notifsvalidate').style.display = "block";
    document.querySelector('#notifsalias').style.display = "block";
}

function addJid(n) {
    n.style.display = "none";
    document.querySelector('#addvalidate').style.display = "block";
    document.querySelector('#addalias').style.display = "block";
    document.querySelector('#addjid').style.display = "block";
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
