var AccountNext = {
    host : '',
    setHost : function(host) {
        this.host = host;
    },
    setUsername : function(user) {
        document.querySelector('#username').innerHTML = user + '@' + this.host;
    }
}

function setUsername(user) {
    AccountNext.setUsername(user);
}
