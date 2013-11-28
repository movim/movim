var Session = {
    session: null,
    init: function() {
        this.session = localStorage.getItem('session');

        if(this.session == "null" || this.session == null) {
            this.clear();

            localStorage.setItem('session', JSON.stringify(this.session));
        } else {
            this.session = JSON.parse(this.session);
        }
    },

    reset: function() {
        localStorage.setItem('session', null);
    },

    clear: function() {
        this.session = {
            'id'    : 0
        };

        localStorage.setItem('session', JSON.stringify(this.session));
    },

    getSession: function() {
        this.init();
        this.session.id = this.session.id+1;

        localStorage.setItem('session', JSON.stringify(this.session));

        return this.session;
    }
}

Session.init();
console.log(Session.session);
/*
$session = array(
        'rid' => 1,
        'sid' => 0,
        'id'  => 0,
        'url' => $serverconfig['boshUrl'],
        'port'=> 5222,
        'host'=> $host,
        'domain' => $domain,
        'ressource' => 'moxl'.substr(md5(date('c')), 3, 6),

        'user'     => $user,
        'password' => $element['pass'],

        'proxyenabled' => $serverconfig['proxyEnabled'],
        'proxyurl' => $serverconfig['proxyURL'],
        'proxyport' => $serverconfig['proxyPort'],
        'proxyuser' => $serverconfig['proxyUser'],
        'proxypass' => $serverconfig['proxyPass']);
*/
