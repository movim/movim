var Login = {
    domain : '@movim.eu',
    submitted : false,

    init : function() {
        // The form submission event
        document.body.addEventListener('submit', function(e) {
            e.preventDefault();

            Login.submitted = true;

            // We register the socket
            MovimWebsocket.connection.register(this.querySelector('input#username').value.replace(/.*@/, ''));

            var button = this.querySelector('input[type=submit]');
            button.value = button.dataset.loading;

            localStorage.username = document.querySelector('input#username').value;

            // A fallback security
            setTimeout('MovimWebsocket.unregister()', 20000);

            return true;
        }, false);
    },

    setCookie : function(key, value, expires) {
        document.cookie = key + '=' + value + '; expires=' + expires + '; path=/';
    },

    setQuick : function(deviceId, login, host, key) {
        localStorage.setItem('quickDeviceId', deviceId);
        localStorage.setItem('quickLogin', login);
        localStorage.setItem('quickHost', host);
        localStorage.setItem('quickKey', key);
    },

    clearQuick : function() {
        localStorage.removeItem('quickDeviceId');
        localStorage.removeItem('quickLogin');
        localStorage.removeItem('quickHost');
        localStorage.removeItem('quickKey');
    },

    quickLogin : function() {
        if (localStorage.getItem('quickHost') != null
        && localStorage.getItem('quickKey') != null) {
            Login_ajaxQuickLogin(
                localStorage.getItem('quickDeviceId'),
                localStorage.getItem('quickLogin'),
                localStorage.getItem('quickKey'),
                true // check is we can actually quick login before registering
            );
        }
    },

    quickLoginRegister : function () {
        MovimWebsocket.connection.register(localStorage.getItem('quickHost'));
    }
}

MovimWebsocket.attach(function()
{
    Login.init();

    // We enable the form
    var inputs = document.querySelectorAll('#login_widget div input[disabled]');
    for (var i = 0; i < inputs.length; i++)
    {
        inputs[i].disabled = false;
    }
});

MovimWebsocket.start(function() {
    Login.quickLogin();
});

MovimWebsocket.register(function()
{
    if (localStorage.getItem('quickKey') != null) {
        Login_ajaxQuickLogin(
            localStorage.getItem('quickDeviceId'),
            localStorage.getItem('quickLogin'),
            localStorage.getItem('quickKey')
        );
    } else {
        form = document.querySelector('form[name="login"]');
        if (Login.submitted) {
            Login_ajaxLogin(MovimUtils.formToJson('login'));
        }
    }
});

movimAddOnload(function() {
    // We had the autocomplete system
    var login = document.querySelector('input#username');

    login.addEventListener('input', function() {
        if (this.value.indexOf('@') == -1) {
            document.querySelector('input#complete').value = this.value + '@' + Login.domain;
        } else {
            document.querySelector('input#complete').value = this.value;
        }

        if (this.value.length == 0) {
            document.querySelector('input#complete').value = '';
        }
    });

    login.addEventListener('blur', function() {
        this.value = document.querySelector('input#complete').value;
    });
});
