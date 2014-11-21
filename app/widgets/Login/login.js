function fillExample(login, pass) {
    document.querySelector('#login').value = login;
    document.querySelector('#pass').value = pass;
}

/**
 * @brief Save a jid in the local storage
 * @param The jid to remember
 */
function rememberSession(jid) {
    if(localStorage['previousSessions'] == null) {
        localStorage.setObject('previousSessions', new Array());
    }

    var s = localStorage.getObject('previousSessions');
    if(s.indexOf(jid) == -1) {
        s.push(jid);
        localStorage.setObject('previousSessions', s);
    }
}

/**
 * @brief Choose a session to connect and show the login form
 * @param The jid to choose
 */
function chooseSession(jid) {
    movim_remove_class('#loginpage', 'choose');
    document.querySelector('#login').value = jid;
    
    if(jid != '') {
        document.querySelector('#pass').focus();
    } else {
        document.querySelector('#login').focus();
    }
}

/**
 * @brief Remove a remembered session
 * @param The jid to choose
 */
function removeSession(jid) {
    var s = localStorage.getObject('previousSessions');
    s.splice(s.indexOf(jid), 1);

    if(s.length == 0) {
        localStorage.removeItem('previousSessions');
        movim_remove_class('#loginpage', 'choose');
    } else {
        localStorage.setObject('previousSessions', s);
    }
    
    Login_ajaxGetRememberedSession(localStorage.getItem('previousSessions'));
}

/**
 * @brief Back to the choosing panel
 */
function backToChoose() {
    movim_add_class('#loginpage', 'choose');
}

/**
 * @brief Post login requests
 */
function postLogin(jid, url) {
    rememberSession(jid);
    localStorage.postStart = 1;
    movim_reload(url);
}

MovimWebsocket.attach(function()
{
    if(localStorage.username != null)
        document.querySelector('#login').value = localStorage.username;

    // The form submission event
    form = document.querySelector('form[name="login"]');
    form.onsubmit = function(e) {
        e.preventDefault();

        var button = this.querySelector('input[type=submit]');
        button.className = 'button color orange icon yes';
        button.value = button.dataset.loading;
        
        eval(this.dataset.action);

        localStorage.username = document.querySelector('#login').value;
        rememberSession(localStorage.username);

        // A fallback security
        setTimeout("MovimWebsocket.unregister()", 15000);
    }

    // We hide the Websocket error
    document.querySelector('#loginpage #warning .websocket').style.display = 'none';

    // We enable the form
    var inputs = document.querySelectorAll('#loginpage input[disabled]');
    for (var i = 0; i < inputs.length; i++)
    {
        inputs[i].disabled = false;
    }

    // We get the previous sessions
    Login_ajaxGetRememberedSession(localStorage.getItem('previousSessions'));

    if(localStorage.getItem('previousSessions') != null) {
        movim_add_class('#loginpage', 'choose');
    }
});

movim_add_onload(function() {
    //MovimWebsocket.unregister();
    
    /* Dump cache variables  in localStorage */
    for ( var i = 0, len = localStorage.length; i < len; ++i ) {
        var cache = localStorage.key(i);
        if(cache.indexOf("_cache", 6) !== -1)
            localStorage.removeItem(cache);
    }
});
