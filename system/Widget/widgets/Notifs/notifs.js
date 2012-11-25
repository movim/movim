function showNotifsList() {
    show = document.getElementById('notifslist');
    
    hideLogoutList();

    if(show.style.display == 'block')
        show.style.display = 'none';
    else
        show.style.display = 'block';
}

function hideNotifsList() {
    document.getElementById('notifslist').style.display = 'none';
}
/*function hideNotification(n) {
    n.parentNode.parentNode.removeChild(n.parentNode);
}

function showAlias(n) {
    n.style.display = "none";
    document.querySelector('#notifsvalidate').style.display = "block";
    document.querySelector('#labelnotifsalias').style.display = "block";
    document.querySelector('#notifsalias').style.display = "block";
}

function addJid(n) {
    document.querySelector('#addcontact').style.display = "block";
}

function cancelAddJid() {
    document.querySelector('#addcontact').style.display = "none";
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
*/
