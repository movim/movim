function ChatOmemoStorage() {
    this.jid = USER_JID;
}

ChatOmemoStorage.prototype = {
    Direction: {
        SENDING: 1,
        RECEIVING: 2,
    },

    put: function (key, value) {
        if (key === undefined || value === undefined || key === null || value === null)
            throw new Error("Tried to store undefined/null");

        return localStorage.setObject(key, value);
    },
    get: function (key, defaultValue) {
        if (key === null || key === undefined)
            throw new Error("Tried to get value for undefined/null key");
        if (key in localStorage) {
            return localStorage.getObject(key);
        } else {
            return defaultValue;
        }
    },
    remove: function (key) {
        if (key === null || key === undefined)
            throw new Error("Tried to remove value for undefined/null key");

        localStorage.removeItem(key);
    },

    setIdentityKeyPair: function(identityKeyPair) {
        return Promise.resolve(this.put(this.jid + '.identityKey', {
            'privKey': MovimUtils.arrayBufferToBase64(identityKeyPair.privKey),
            'pubKey': MovimUtils.arrayBufferToBase64(identityKeyPair.pubKey)
        }));
    },
    getIdentityKeyPair: function () {
        identityKeyPair = this.get(this.jid + '.identityKey');

        if (!identityKeyPair) return Promise.reject();

        return Promise.resolve({
            'privKey': MovimUtils.base64ToArrayBuffer(identityKeyPair.privKey),
            'pubKey': MovimUtils.base64ToArrayBuffer(identityKeyPair.pubKey)
        });
    },

    setLocalRegistrationId: function (registrationId) {
        return Promise.resolve(this.put(this.jid + '.registrationId', registrationId));
    },
    getLocalRegistrationId: function () {
        return Promise.resolve(this.get(this.jid + '.registrationId'));
    },

    isTrustedIdentity: function (identifier, identityKey, direction) {
        if (identifier === null || identifier === undefined) {
            throw new Error("tried to check identity key for undefined/null key");
        }
        if (!(identityKey instanceof ArrayBuffer)) {
            throw new Error("Expected identityKey to be an ArrayBuffer");
        }
        var trusted = this.get(this.jid + '.identityKey' + identifier);
        if (trusted === undefined) {
            return Promise.resolve(true);
        }

        return Promise.resolve(libsignal.util.toString(identityKey) === libsignal.util.toString(trusted));
    },
    loadIdentityKey: function (identifier) {
        if (identifier === null || identifier === undefined)
            throw new Error("Tried to get identity key for undefined/null key");
        return Promise.resolve(MovimUtils.base64ToArrayBuffer(this.get(this.jid + '.identityKey' + identifier)));
    },
    saveIdentity: function (identifier, identityKey) {
        if (identifier === null || identifier === undefined)
            throw new Error("Tried to put identity key for undefined/null key");

        var address = new libsignal.SignalProtocolAddress.fromString(identifier);

        var existing = this.get(this.jid + '.identityKey' + address.getName());
        this.put(this.jid + '.identityKey' + address.getName(), MovimUtils.arrayBufferToBase64(identityKey))

        if (existing && toString(identityKey) !== toString(existing)) {
            return Promise.resolve(true);
        } else {
            return Promise.resolve(false);
        }

    },

    loadPreKey: function (keyId) {
        var res = this.get(this.jid + '.25519KeypreKey' + keyId);
        if (res !== undefined) {
            res = {
                pubKey: MovimUtils.base64ToArrayBuffer(res.pubKey),
                privKey: MovimUtils.base64ToArrayBuffer(res.privKey)
            };
        }
        return Promise.resolve(res);
    },
    storePreKey: function (keyId, keyPair) {
        keyPair.pubKey = MovimUtils.arrayBufferToBase64(keyPair.pubKey);
        keyPair.privKey = MovimUtils.arrayBufferToBase64(keyPair.privKey);
        return Promise.resolve(this.put(this.jid + '.25519KeypreKey' + keyId, keyPair));
    },
    removePreKey: function (keyId) {
        return Promise.resolve(this.remove(this.jid + '.25519KeypreKey' + keyId));
    },

    loadSignedPreKey: function (keyId) {
        var res = this.get(this.jid + '.25519KeysignedKey' + keyId);
        if (res !== undefined) {
            res = {
                keyPair: {
                    pubKey: MovimUtils.base64ToArrayBuffer(res.keyPair.pubKey),
                    privKey: MovimUtils.base64ToArrayBuffer(res.keyPair.privKey),
                },
                signature: MovimUtils.base64ToArrayBuffer(res.signature),
                keyId: keyId
            };
        }
        return Promise.resolve(res);
    },
    storeSignedPreKey: function (keyId, key) {
        key.keyPair.pubKey = MovimUtils.arrayBufferToBase64(key.keyPair.pubKey);
        key.keyPair.privKey = MovimUtils.arrayBufferToBase64(key.keyPair.privKey);
        key.signature = MovimUtils.arrayBufferToBase64(key.signature);
        return Promise.resolve(this.put(this.jid + '.25519KeysignedKey' + keyId, key));
    },
    removeSignedPreKey: function (keyId) {
        return Promise.resolve(this.remove(this.jid + '.25519KeysignedKey' + keyId));
    },

    loadSession: function (identifier) {
        return Promise.resolve(this.get(this.jid + '.session' + identifier));
    },
    storeSession: function (identifier, record) {
        return Promise.resolve(this.put(this.jid + '.session' + identifier, record));
    },
    removeSession: function (identifier) {
        return Promise.resolve(this.remove(this.jid + '.session' + identifier));
    },
    removeAllSessions: function () {
        let filtered = Object.keys(localStorage).filter(key => key.startsWith(this.jid + '.session'));

        for (id in filtered) {
            this.remove(filtered[key]);
        }

        return Promise.resolve();
    },
    checkJidHasSessions: function (identifier) {
        return Object.keys(localStorage).filter(key => key.startsWith(this.jid + '.session' + identifier)).length > 0;
    },
    removeAllSessionsOfJid: function (identifier) {
        let filtered = Object.keys(localStorage).filter(key => key.startsWith(this.jid + '.session' + identifier));

        for (id in filtered) {
            this.remove(filtered[id]);
        }

        this.remove(this.jid + '.contact' + identifier);

        return Promise.resolve();
    },

    setContactState: function (jid, state) {
        return Promise.resolve(this.put(this.jid + '.contact' + jid, state));
    },
    hasContactState: function(jid) {
        return (this.get(this.jid + '.contact' + jid) !== undefined);
    },
    getContactState: function (jid) {
        let state = Boolean(this.get(this.jid + '.contact' + jid));
        return Promise.resolve(state);
    },

    toString: function(thing) {
        if (typeof thing == 'string') {
            return thing;
        }
        return new dcodeIO.ByteBuffer.wrap(thing).toString('binary');
    }
};