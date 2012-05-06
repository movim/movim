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
    document.querySelector('#addrefuse').style.display = "block";
    document.querySelector('#addalias').style.display = "block";
    document.querySelector('#addjid').style.display = "block";
}

function cancelAddJid() {
    document.querySelector('#addvalidate').style.display = "none";
    document.querySelector('#addrefuse').style.display = "none";
    document.querySelector('#addalias').style.display = "none";
    document.querySelector('#addjid').style.display = "none";
    document.querySelector('#addstart').style.display = "inline";
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

function RequestPermission (callback)
{
    window.webkitNotifications.requestPermission(callback);
}

function notification(params)
{      
    if (window.webkitNotifications.checkPermission() > 0) {
        RequestPermission(notification);
    }

    if(document_focus == false) {
        var icon  = '';
        var title = params[0];
        var body  = params[1];
        
        var popup = window.webkitNotifications.createNotification(icon, title, body);
        popup.show();
        setTimeout(function(){
        popup.cancel();
        }, '30');
    }
}
