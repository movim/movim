var Login = {
    fillExample : function(login, pass) {
        document.querySelector('#login').value = login;
        document.querySelector('#pass').value = pass;
    },

    /**
     * @brief Init the main form behaviour
     */
    init : function() {
        // The form submission event
        form = document.querySelector('form[name="login"]');
        form.onsubmit = function(e) {
            e.preventDefault();

            // We register the socket
            MovimWebsocket.connection.register(this.querySelector('input#login').value.replace(/.*@/, ""));

            var button = this.querySelector('input[type=submit]');
            button.value = button.dataset.loading;

            localStorage.username = document.querySelector('#login').value;
            Login.rememberSession(localStorage.username);

            // A fallback security
            setTimeout("MovimWebsocket.unregister()", 20000);
        }
    },
    
    refresh: function(){
        /*Add onclick listeners*/
        var sessions = document.querySelectorAll('#sessions section ul > li');
        var i = 0;
        
        while(i < sessions.length)
        {
            sessions[i].onclick = function(e){Login.choose(e.target);};
            i++;
        }
    },

    /**
     * @brief Save a jid in the local storage
     * @param The jid to remember
     */
    rememberSession : function(jid) {
        if(localStorage['previousSessions'] == null) {
            localStorage.setObject('previousSessions', new Array());
        }

        var s = localStorage.getObject('previousSessions');
        if(s.indexOf(jid) == -1 && jid != '') {
            s.push(jid);
            localStorage.setObject('previousSessions', s);
        }
    },

    /**
     * @brief Choose a session to connect and show the login form
     * @param The jid to choose
     */
    choose : function(element) {
        var tn = element.tagName;
        while(element.tagName != "LI")
            element = element.parentNode;
        var jid = element.id;
        
        if(tn == "I" || tn == "DIV"){
            Login.removeSession(jid);
        }
        else{
            Login.toForm();
            
            document.querySelector('#login').value = jid;
            document.querySelector('#pass').value = "";
            
            if(jid != '') {
                document.querySelector('#pass').focus();
            } else {
                document.querySelector('#login').focus();
            }
        }
    },

    /**
     * @brief Remove a remembered session
     * @param The jid to choose
     */
    removeSession : function(jid) {
        var s = localStorage.getObject('previousSessions');
        s.splice(s.indexOf(jid), 1);

        if(s.length == 0) {
            localStorage.removeItem('previousSessions');
            Login.toForm();
        } else {
            localStorage.setObject('previousSessions', s);
        }
        
        Login_ajaxGetRememberedSession(localStorage.getItem('previousSessions'));
    },

    /**
     * @brief Back to the choosing panel
     */
    toChoose : function() {
        movim_add_class('#login_widget', 'choose');
    },

    /**
     * @brief Back to the choosing panel
     */
    toForm : function() {
        movim_remove_class('#login_widget', 'choose');
        // Empty login field
        document.querySelector('#login').value = "";
    },

    /**
     * @brief Post login requests
     */
    post : function(jid, url) {
        Login.rememberSession(jid);
        localStorage.postStart = 1;
        movim_reload(url);
    },

    /**
     * @brief Set the Movim cookie
     */
    setCookie : function(value) {
        document.cookie = 'MOVIM_SESSION_ID='+value; 
    } 
}

MovimWebsocket.attach(function()
{
    if(localStorage.username != null)
        document.querySelector('#login').value = localStorage.username;

    Login.init();

    // We hide the Websocket error
    document.querySelector('#error_websocket').style.display = 'none';

    // We enable the form
    var inputs = document.querySelectorAll('#login_widget div input[disabled]');
    for (var i = 0; i < inputs.length; i++)
    {
        inputs[i].disabled = false;
    }

    // We get the previous sessions
    Login_ajaxGetRememberedSession(localStorage.getItem('previousSessions'));

    if(localStorage.getItem('previousSessions') != null) {
        Login.toChoose();
    }
});

MovimWebsocket.register(function()
{
    form = document.querySelector('form[name="login"]');
    eval(form.dataset.action);
});

movim_add_onload(function() {
    /* Dump cache variables  in localStorage */
    for ( var i = 0, len = localStorage.length; i < len; ++i ) {
        var cache = localStorage.key(i);
        if(cache.indexOf("_cache", 6) !== -1)
            localStorage.removeItem(cache);
    }
});
