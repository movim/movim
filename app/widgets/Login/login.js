var Login = {
    domain : '@movim.eu',
    submitted : false,
    fillExample : function(login, pass) {
        document.querySelector('input#username').value = login;
        document.querySelector('input#password').value = pass;
    },

    /**
     * @brief Init the main form behaviour
     */
    init : function() {
        // The form submission event
        document.body.addEventListener('submit', function(e) {
            e.preventDefault();

            Login.submitted = true;

            // We register the socket
            MovimWebsocket.connection.register(this.querySelector('input#username').value.replace(/.*@/, ""));

            var button = this.querySelector('input[type=submit]');
            button.value = button.dataset.loading;

            localStorage.username = document.querySelector('input#username').value;
            Login.rememberSession(localStorage.username);

            // A fallback security
            setTimeout("MovimWebsocket.unregister()", 20000);

            return true;
        }, false);
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

            document.querySelector('input#username').value = jid;
            document.querySelector('input#complete').value = jid;
            document.querySelector('input#password').value = "";

            if(jid != '') {
                document.querySelector('input#password').focus();
            } else {
                document.querySelector('input#username').focus();
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
        MovimUtils.addClass('#login_widget', 'choose');
    },

    /**
     * @brief Back to the choosing panel
     */
    toForm : function() {
        MovimUtils.removeClass('#login_widget', 'choose');
        // Empty login field
        document.querySelector('input#username').value = "";
    },

    /**
     * @brief Post login requests
     */
    post : function(jid, url) {
        Login.rememberSession(jid);
        localStorage.postStart = 1;

        MovimUtils.redirect(url);
    },

    /**
     * @brief Set the Movim cookie
     */
    setCookie : function(value) {
        document.cookie = 'MOVIM_SESSION_ID='+value;
    },
}

MovimWebsocket.attach(function()
{
    if(localStorage.username != null)
        document.querySelector('input#username').value = localStorage.username;

    Login.init();

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
    if(Login.submitted) {
        eval(form.dataset.action);
    }
});

movim_add_onload(function() {
    // We had the autocomplete system
    var login = document.querySelector('input#username');
    login.addEventListener('input', function() {
        if(this.value.indexOf('@') == -1) {
            // TODO allow another server here
            document.querySelector('input#complete').value = this.value + '@' + Login.domain;
        } else {
            document.querySelector('input#complete').value = this.value;
        }

        if(this.value.length == 0) {
            document.querySelector('input#complete').value = '';
        }
    });

    login.addEventListener('blur', function() {
        this.value = document.querySelector('input#complete').value;
    });

    /* Dump cache variables  in localStorage */
    for ( var i = 0, len = localStorage.length; i < len; ++i ) {
        var cache = localStorage.key(i);
        if(cache.indexOf("_cache", 6) !== -1)
            localStorage.removeItem(cache);
    }
});
