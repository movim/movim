var KeyHelper = libsignal.KeyHelper;

const KEY_ALGO = {
    'name': 'AES-GCM',
    'length': 128
};
const NUM_PREKEYS = 50;
const SIGNED_PREKEY_ID = 1;

var ChatOmemo = {
    requestedDevicesListFrom: null,

    initGenerateBundle: async function() {
        var store = new ChatOmemoStorage();
        const localDeviceId = await store.getLocalRegistrationId();

        if (localDeviceId == undefined) {
            ChatOmemo.generateBundle();
        } else {
            ChatOmemo_ajaxGetSelfMissingSessions(store.getSessionsIds(USER_JID));
        }
    },

    generateBundle: async function () {
        ChatOmemo_ajaxNotifyGeneratingBundle();
    },

    doGenerateBundle: async function () {
        var store = new ChatOmemoStorage();

        store.removeAllSessions();

        const identityKeyPair = await KeyHelper.generateIdentityKeyPair();
        const bundle = {};

        const localDeviceId = await store.getLocalRegistrationId();
        const deviceId = localDeviceId ?? KeyHelper.generateRegistrationId();

        bundle['identityKey'] = MovimUtils.arrayBufferToBase64(identityKeyPair.pubKey);
        bundle['deviceId'] = deviceId;

        store.setLocalRegistrationId(deviceId);
        store.setIdentityKeyPair(identityKeyPair);

        const signedPreKey = await KeyHelper.generateSignedPreKey(identityKeyPair, SIGNED_PREKEY_ID);

        store.storeSignedPreKey(signedPreKey.keyId, signedPreKey);
        bundle['signedPreKey'] = {
            'id': signedPreKey.keyId,
            'publicKey': signedPreKey.keyPair.pubKey,
            'signature': signedPreKey.signature
        }
        const keys = await Promise.all(MovimUtils.range(0, NUM_PREKEYS).map(id => KeyHelper.generatePreKey(id)));
        keys.forEach(k => store.storePreKey(k.keyId, k.keyPair));

        const preKeys = keys.map(k => ({ 'id': k.keyId, 'key': k.keyPair.pubKey }));
        bundle['preKeys'] = preKeys;

        ChatOmemo_ajaxNotifyGeneratedBundle();
        ChatOmemo_ajaxAnnounceBundle(bundle);
    },

    refreshBundle: async function() {
        var store = new ChatOmemoStorage();

        const bundle = {};

        // We get the base of the bundle from the store

        let keyPair = await store.getIdentityKeyPair();

        bundle['identityKey'] = MovimUtils.arrayBufferToBase64(keyPair.pubKey);
        bundle['deviceId'] = await store.getLocalRegistrationId();

        let signedPreKey = store.loadCompleteSignedPreKey(SIGNED_PREKEY_ID);
        bundle['signedPreKey'] = {
            'id': signedPreKey.keyId,
            'publicKey': MovimUtils.arrayBufferToBase64(signedPreKey.keyPair.pubKey),
            'signature': MovimUtils.arrayBufferToBase64(signedPreKey.signature)
        }

        // We refresh all the preKeys

        const keys = await Promise.all(MovimUtils.range(0, NUM_PREKEYS).map(id => KeyHelper.generatePreKey(id)));
        keys.forEach(k => store.storePreKey(k.keyId, k.keyPair));

        const preKeys = keys.map(k => ({ 'id': k.keyId, 'key': k.keyPair.pubKey }));
        bundle['preKeys'] = preKeys;

        ChatOmemo_ajaxAnnounceBundle(bundle);
    },

    handlePreKeys: function (jid, preKeys) {
        let promises = [];

        Object.entries(preKeys).forEach(([deviceId, preKey]) => {
            promises.push(ChatOmemo.handlePreKey(jid, deviceId, preKey));
        });

        Promise.all(promises).then(results => {
            var store = new ChatOmemoStorage();

            /**
             * First time we handle a session, we enforce the OMEMO state to true
             */
            if (!store.hasContactState(jid)) {
                store.setContactState(jid, true);
            }

            Chat.setOmemoState('yes');
            Chat.disableSending();

            if (Chat.getTextarea() && Chat.getTextarea().value.length > 0) {
                Chat.sendMessage();
            }
        });
    },
    handlePreKey: async function (jid, deviceId, preKey) {
        var store = new ChatOmemoStorage();
        var address = new libsignal.SignalProtocolAddress(jid, deviceId);

        const session = await store.loadSession(address.toString());

        // If we already have a session we don't have to build it
        if (session) {
            var promise = Promise.resolve();
        } else {
            var sessionBuilder = new libsignal.SessionBuilder(store, address);
            var promise = sessionBuilder.processPreKey({
                registrationId: parseInt(deviceId, 10),
                identityKey: MovimUtils.base64ToArrayBuffer(preKey.identitykey),
                signedPreKey: {
                    keyId: parseInt(preKey.signedprekeyid, 10),
                    publicKey: MovimUtils.base64ToArrayBuffer(preKey.signedprekeypublic),
                    signature: MovimUtils.base64ToArrayBuffer(preKey.signedprekeysignature)
                },
                preKey: {
                    keyId: preKey.prekey.id,
                    publicKey: MovimUtils.base64ToArrayBuffer(preKey.prekey.value)
                }
            });
        }

        promise.then(function onsuccess() {
            console.log('success ' + jid + ':' + deviceId);
            store.getLocalRegistrationId().then(localDeviceId => {
                ChatOmemo_ajaxHttpSetBundleSession(jid, deviceId, localDeviceId);
            });

            Chat.setOmemoState('yes');
        });

        promise.catch(function onerror(error) {
            console.log(error);
        });

        return promise;
    },

    encrypt: async function (to, plaintext) {
        var store = new ChatOmemoStorage();

        // https://xmpp.org/extensions/attic/xep-0384-0.3.0.html#usecases-messagesend

        let iv = crypto.getRandomValues(new Uint8Array(12));
        let key = await crypto.subtle.generateKey(KEY_ALGO, true, ['encrypt', 'decrypt']);

        let algo = {
            'name': 'AES-GCM',
            'iv': iv,
            'tagLength': 128
        };

        let encrypted = await crypto.subtle.encrypt(algo, key, MovimUtils.stringToArrayBuffer(plaintext));
        let length = encrypted.byteLength - ((128 + 7) >> 3);
        let ciphertext = encrypted.slice(0, length);
        let tag = encrypted.slice(length);
        let exportedKey = await crypto.subtle.exportKey('raw', key);

        // obj
        let keyAndTag = MovimUtils.appendArrayBuffer(exportedKey, tag);
        let biv = MovimUtils.arrayBufferToBase64(iv);
        let payload = MovimUtils.arrayBufferToBase64(ciphertext);
        let deviceId = await store.getLocalRegistrationId();
        let remoteKeys = await this.encryptJid(keyAndTag, to);
        let ownKeys = await this.encryptJid(keyAndTag, store.jid);

        remoteKeys = remoteKeys.concat(ownKeys);

        let messageKeys = {};
        remoteKeys.map(result => {
            messageKeys[result.device] = {
                payload : btoa(result.payload.body),
                prekey : 3 == parseInt(result.payload.type, 10)
            };
        });

        return {
            'sid': deviceId,
            'keys': messageKeys,
            'iv': biv,
            'payload': payload
        };
    },
    decrypt: async function (message) {
        if (message.omemoheader == undefined) return;

        let maybeDecrypted = await ChatOmemoDB.getMessage(message.id);

        if (maybeDecrypted !== undefined) {
            return maybeDecrypted;
        }

        if (message.omemoheader.keys == undefined) return;

        var store = new ChatOmemoStorage();
        let deviceId = await store.getLocalRegistrationId();
        let originalSessionsNumber = store.getSessions(message.jidfrom).length;

        if (message.omemoheader.keys[deviceId] == undefined) {
            console.log('Message not encrypted for this device');
            return;
        }

        let key = message.omemoheader.keys[deviceId];
        let plainKey;

        try {
            plainKey = await this.decryptDevice(MovimUtils.base64ToArrayBuffer(key.payload), key.prekey, message.jidfrom, message.omemoheader.sid);
        } catch (err) {
            console.log('Error during decryption: ' + err);
            return;
        }

        let exportedAESKey = plainKey.slice(0, 16);
        let authenticationTag = plainKey.slice(16);

        if (authenticationTag.byteLength < 16) {
            if (authenticationTag.byteLength > 0) {
            throw new Error('Authentication tag too short');
            }

            console.log(`Authentication tag is only ${authenticationTag.byteLength} byte long`);
        }

        if (!message.omemoheader.payload) {
            console.log('No payload to decrypt');
        }

        // One of our key was used, lets refresh the bundle
        if (key.prekey) {
            ChatOmemo.refreshBundle();
        }

        let iv = MovimUtils.base64ToArrayBuffer(message.omemoheader.iv);
        let ciphertextAndAuthenticationTag = MovimUtils.appendArrayBuffer(
            MovimUtils.base64ToArrayBuffer(message.omemoheader.payload),
            authenticationTag
        );

        let importedKey = await crypto.subtle.importKey('raw', exportedAESKey, 'AES-GCM', false, ['decrypt']);
        let decryptedBuffer = await crypto.subtle.decrypt({
            name: 'AES-GCM',
            iv,
            tagLength: 128
        }, importedKey, ciphertextAndAuthenticationTag);

        let plaintext = MovimUtils.arrayBufferToString(decryptedBuffer);

        /**
         * We received a message for us, and a session was created from it, we might have
         * some more sessions to build
         */
        if (store.getSessions(message.jidfrom).length > originalSessionsNumber
        && Object.keys(message.omemoheader.keys).includes(String(deviceId))
        && ChatOmemo.requestedDevicesListFrom != message.jidfrom) {
            console.log('A new session was created from the incoming message, refresh the contact devices');

            ChatOmemo.requestedDevicesListFrom = message.jidfrom;
            ChatOmemo_ajaxGetDevicesList(message.jidfrom);
        }

        ChatOmemoDB.putMessage(message.id, plaintext);
        return plaintext;
    },
    enableContactState: function (jid) {
        var store = new ChatOmemoStorage();
        store.setContactState(jid, true);
        Chat_ajaxGet(jid);
        ChatOmemo_ajaxEnableContactState();
    },
    disableContactState: function (jid) {
        var store = new ChatOmemoStorage();
        store.setContactState(jid, false);
        Chat.setOmemoState("disabled");
        ChatOmemo_ajaxDisableContactState();
    },
    getContactState: async function(jid) {
        var store = new ChatOmemoStorage();
        return store.getContactState(jid);
    },
    encryptJid: function (plaintext, jid) {
        var store = new ChatOmemoStorage();
        let promises = store.filter('.session' + jid)
            .map(key => key.split(/[\s.]+/).pop())
            .map(deviceId => this.encryptDevice(plaintext, jid, deviceId) );

        return Promise.all(promises).then(result => {
            return result;
        });
    },
    encryptDevice: function (plaintext, jid, deviceId) {
        var address = new libsignal.SignalProtocolAddress(jid, parseInt(deviceId, 10));
        var store = new ChatOmemoStorage();
        var sessionCipher = new libsignal.SessionCipher(store, address);

        return sessionCipher.encrypt(plaintext)
            .then(payload => ({ 'payload': payload, 'device': deviceId }));
    },
    decryptDevice: async function(ciphertext, preKey, jid, deviceId) {
        var address = new libsignal.SignalProtocolAddress(jid, parseInt(deviceId, 10));
        var store = new ChatOmemoStorage();
        var sessionCipher = new libsignal.SessionCipher(store, address);

        return (preKey)
            ? await sessionCipher.decryptPreKeyWhisperMessage(ciphertext, 'binary')
            : await sessionCipher.decryptWhisperMessage(ciphertext, 'binary');
    }
}

MovimWebsocket.attach(function() {
    ChatOmemo.initGenerateBundle();
});