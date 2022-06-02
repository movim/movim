function ChatOmemoStorage() {
    this.jid = USER_JID;
}

ChatOmemoStorage.prototype = {
    Direction: {
        SENDING: 1,
        RECEIVING: 2,
    },

    // Storage is versionned to ensure a proper reset when changes are made
    storageVersion: '1',

    // Generic methods
    put: function (key, value) {
        if (key === undefined || value === undefined || key === null || value === null)
            throw new Error("Tried to store undefined/null");

        return localStorage.setObject(this.storageVersion + '.' + key, value);
    },
    get: function (key, defaultValue) {
        if (key === null || key === undefined)
            throw new Error("Tried to get value for undefined/null key");
        if (this.storageVersion + '.' + key in localStorage) {
            return localStorage.getObject(this.storageVersion + '.' + key);
        } else {
            return defaultValue;
        }
    },
    remove: function (key) {
        if (key === null || key === undefined)
            throw new Error("Tried to remove value for undefined/null key");

        localStorage.removeItem(this.storageVersion + '.' + key);
    },
    filter: function (search) {
        return Object.keys(localStorage).filter(key => key.startsWith(this.storageVersion + '.' + this.jid + search));
    },

    // OMEMO specific methods
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

    loadCompleteSignedPreKey: function (keyId) {
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
        return res;
    },
    loadSignedPreKey: function (keyId) {
        return Promise.resolve(this.loadCompleteSignedPreKey(keyId).keyPair);
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
        let filtered = this.filter('.session');

        for (key in filtered) {
            localStorage.removeItem(filtered[key]);
        }

        return Promise.resolve();
    },
    getSessions: function (identifier) {
        return this.filter('.session' + identifier);
    },
    getSessionsIds: function (identifier) {
        return this.filter('.session' + identifier).map(sessionId => sessionId.split('.').pop());
    },
    checkJidHasSessions: function (identifier) {
        return this.filter('.session' + identifier).length > 0;
    },
    removeAllSessionsOfJid: function (identifier) {
        let filtered = this.filter('.session' + identifier);

        for (id in filtered) {
            localStorage.removeItem(filtered[id]);
        }

        this.remove(this.jid + '.contact' + identifier);

        return Promise.resolve();
    },

    setContactState: function (jid, state) {
        return Promise.resolve(this.put(this.jid + '.contact' + jid, state));
    },
    removeContactState: function (jid) {
        return Promise.resolve(this.remove(this.jid + '.contact' + jid));
    },
    hasContactState: function(jid) {
        return (this.get(this.jid + '.contact' + jid) !== undefined);
    },
    getContactState: function (jid) {
        let state = Boolean(this.get(this.jid + '.contact' + jid));
        return Promise.resolve(state);
    },

    getSessionState: function (identifier) {
        return this.loadSession(identifier).then(json => {
            if (json !== undefined) {
                let session = JSON.parse(json);
                return (session.state === true || session.state === undefined);
            }

            return false;
        });
    },
    setSessionState: function (identifier, enabled) {
        this.loadSession(identifier).then(json => {
            if (json !== undefined) {
                let session = JSON.parse(json);
                session.state = Boolean(enabled);
                this.storeSession(identifier, JSON.stringify(session));
            }
        });
    },

    toString: function(thing) {
        if (typeof thing == 'string') {
            return thing;
        }
        return new dcodeIO.ByteBuffer.wrap(thing).toString('binary');
    }
};