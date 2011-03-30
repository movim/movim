var myhash = null;

tests.set('hash', H({
    creation: function()
    {
        object = {test: 'prout'};
        myhash = H(object);
        equals(myhash.container, object);

        myhash = new Hash();
        different(myhash, null);
    },

    ishash: function()
    {
        var obj = new Array();
        assert(isHash(myhash));
        nassert(isHash(obj));
    },

    get: function()
    {
        hash = H({toto: "tata", prout: "tagada"});
        equals(hash.get("toto"), "tata");
    },

    set: function()
    {
        myhash.set("toto", "tata");
        myhash.set("prout", "tagada");

        equals(myhash.get("toto"), "tata");
        equals(myhash.get("prout"), "tagada");

        myhash.set("toto", "tigidi");
        equals(myhash.get("toto"), "tigidi");
    },

    tostring: function()
    {
        var hash = H({toto: "tata", prout: "tagada"});
        equals(hash.to_string(), 'toto: tata, prout: tagada');
    },

    change: function()
    {
        myhash.set('prout', 'tigidi');
        equals(myhash.get('prout'), 'tigidi');
    },

    iterate: function()
    {
        var iter = myhash.iterate();
        while(iter.next()) {
            equals(iter.val(), myhash.get(iter.key()))
        }

        iter.end();
        while(iter.prev()) {
            equals(iter.val(), myhash.get(iter.key()))
        }
    },

    keys: function()
    {
        hash = H({toto: "tata", prout: "tagada"});
        keys = new Array('toto', 'prout');
        vals = new Array('tata', 'tagada');

        hashkeys = hash.keys();
        hashvals = hash.values();

        assert(keys[0] == hashkeys[0] && keys[1] == hashkeys[1]);
        assert(vals[0] == hashvals[0] && vals[1] == hashvals[1]);
    },
}));