/**
 * Movim Session class.
 *
 * Implement an additional security to prevent same rid and id in the
 * XMPP requests during the session
 */
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

    check: function() {
        if(SESSION_RID != null && SESSION_RID > this.session.rid)
            this.session.rid = SESSION_RID;
        if(SESSION_ID != null && SESSION_ID > this.session.id)
            this.session.id = SESSION_ID;
    },

    clear: function() {
        this.session = {
            'id'    : 0,
            'rid'   : 0
        };

        localStorage.setItem('session', JSON.stringify(this.session));
    },

    getSession: function() {
        console.log('Increase '+this.session.rid);
        this.init();
        this.check();
        this.session.id = this.session.id+1;
        this.session.rid = this.session.rid+1;

        localStorage.setItem('session', JSON.stringify(this.session));

        return this.session;
    }
}

Session.init();
