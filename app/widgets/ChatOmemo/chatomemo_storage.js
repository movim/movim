function ChatOmemoStorage() {
    this.store = {};
}

ChatOmemoStorage.prototype = {
    Direction: {
        SENDING: 1,
        RECEIVING: 2,
    },

    setIdentityKeyPair: function(identityKeyPair) {
        return Promise.resolve(this.put('identityKey', {
            'privKey': MovimUtils.arrayBufferToBase64(identityKeyPair.privKey),
            'pubKey': MovimUtils.arrayBufferToBase64(identityKeyPair.pubKey)
        }));
    },

    getIdentityKeyPair: function () {
        identityKeyPair = this.get('identityKey');
        return Promise.resolve({
            'privKey': MovimUtils.base64ToArrayBuffer(identityKeyPair.privKey),
            'pubKey': MovimUtils.base64ToArrayBuffer(identityKeyPair.pubKey)
        });
    },
    setLocalRegistrationId: function (registrationId) {
        return Promise.resolve(this.put('registrationId', registrationId));
    },
    getLocalRegistrationId: function () {
        return Promise.resolve(this.get('registrationId'));
    },
    put: function (key, value) {
        if (key === undefined || value === undefined || key === null || value === null)
            throw new Error("Tried to store undefined/null");

        localStorage.setObject(key, value);
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

    isTrustedIdentity: function (identifier, identityKey, direction) {
        if (identifier === null || identifier === undefined) {
            throw new Error("tried to check identity key for undefined/null key");
        }
        if (!(identityKey instanceof ArrayBuffer)) {
            throw new Error("Expected identityKey to be an ArrayBuffer");
        }
        var trusted = this.get('identityKey' + identifier);
        if (trusted === undefined) {
            return Promise.resolve(true);
        }
        return Promise.resolve(true);
        //return Promise.resolve(libsignal.util.toString(identityKey) === libsignal.util.toString(trusted));
    },
    loadIdentityKey: function (identifier) {
        if (identifier === null || identifier === undefined)
            throw new Error("Tried to get identity key for undefined/null key");
        return Promise.resolve(this.get('identityKey' + identifier));
    },
    saveIdentity: function (identifier, identityKey) {
        if (identifier === null || identifier === undefined)
            throw new Error("Tried to put identity key for undefined/null key");

        var address = new libsignal.SignalProtocolAddress.fromString(identifier);

        var existing = this.get('identityKey' + address.getName());
        this.put('identityKey' + address.getName(), identityKey)

        if (existing && toString(identityKey) !== toString(existing)) {
            return Promise.resolve(true);
        } else {
            return Promise.resolve(false);
        }

    },

    /* Returns a prekeypair object or undefined */
    loadPreKey: function (keyId) {
        var res = this.get('25519KeypreKey' + keyId);
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
        return Promise.resolve(this.put('25519KeypreKey' + keyId, keyPair));
    },
    removePreKey: function (keyId) {
        return Promise.resolve(this.remove('25519KeypreKey' + keyId));
    },

    /* Returns a signed keypair object or undefined */
    loadSignedPreKey: function (keyId) {
        var res = this.get('25519KeysignedKey' + keyId);
        if (res !== undefined) {
            res = {
                pubKey: MovimUtils.base64ToArrayBuffer(res.keyPair.pubKey),
                privKey: MovimUtils.base64ToArrayBuffer(res.keyPair.privKey)
            };
        }
        return Promise.resolve(res);
    },
    storeSignedPreKey: function (keyId, key) {
        key.keyPair.pubKey = MovimUtils.arrayBufferToBase64(key.keyPair.pubKey);
        key.keyPair.privKey = MovimUtils.arrayBufferToBase64(key.keyPair.privKey);
        key.signature = MovimUtils.arrayBufferToBase64(key.signature);
        return Promise.resolve(this.put('25519KeysignedKey' + keyId, key));
    },
    removeSignedPreKey: function (keyId) {
        return Promise.resolve(this.remove('25519KeysignedKey' + keyId));
    },

    loadSession: function (identifier) {
        return Promise.resolve(this.get('session' + identifier));
    },
    storeSession: function (identifier, record) {
        return Promise.resolve(this.put('session' + identifier, record));
    },
    removeSession: function (identifier) {
        return Promise.resolve(this.remove('session' + identifier));
    },
    removeAllSessions: function (identifier) {
        for (var id in this.store) {
            if (id.startsWith('session' + identifier)) {
                delete this.store[id];
            }
        }
        return Promise.resolve();
    },
    toString: function(thing) {
        if (typeof thing == 'string') {
            return thing;
        }
        return new dcodeIO.ByteBuffer.wrap(thing).toString('binary');
    }
};