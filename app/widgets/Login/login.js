function fillExample(login, pass) {
    document.querySelector('#login').value = login;
    document.querySelector('#pass').value = pass;
}

Storage.prototype.setObject = function(key, value) {
    this.setItem(key, JSON.stringify(value));
}

Storage.prototype.getObject = function(key) {
    return JSON.parse(this.getItem(key));
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
function postLogin(params) {
    rememberSession(params[0]);
    localStorage.postStart = 1;
    movim_reload(params[1]);
}

movim_add_onload(function()
{
    if(localStorage.username != null)
        document.querySelector('#login').value = localStorage.username;

    form = document.querySelector('form[name="login"]');
    
    form.onsubmit = function(e) {
        e.preventDefault();

        var button = this.querySelector('input[type=submit]');
        button.className = 'button color orange icon yes';
        button.value = button.dataset.loading;
        
        eval(this.dataset.action);

        localStorage.username = document.querySelector('#login').value;
        rememberSession(localStorage.username);
    };

    Login_ajaxGetRememberedSession(localStorage.getItem('previousSessions'));

    if(localStorage.getItem('previousSessions') != null) {
        movim_add_class('#loginpage', 'choose');
    }
});
