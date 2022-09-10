var KeyHelper = libsignal.KeyHelper;

const KEY_ALGO = {
    'name': 'AES-GCM',
    'length': 128
};
const NUM_PREKEYS = 50;
const SIGNED_PREKEY_ID = 1;
const AESGCM_REGEX = /^aesgcm:\/\/([^#]+\/([^\/]+\.([a-z0-9]+)))#([a-z0-9]+)/i;

var ChatOmemo = {
    requestedDevicesListFrom: null,
    refreshed: false,

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

    refreshBundle: async function(publishOnly) {
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

        // We refresh or load all the preKeys

        let keys = [];
        let preKeys = [];

        if (publishOnly) {
            keys = await Promise.all(MovimUtils.range(0, NUM_PREKEYS).map(id => store.loadPreKey(id)));

            let counter = -1;
            preKeys = keys.map(k => {
                counter++;
                return { 'id': counter, 'key': MovimUtils.arrayBufferToBase64(k.pubKey) };
            });
        } else {
            keys = await Promise.all(MovimUtils.range(0, NUM_PREKEYS).map(id => KeyHelper.generatePreKey(id)));
            keys.forEach(k => store.storePreKey(k.keyId, k.keyPair));
            preKeys = keys.map(k => ({ 'id': k.keyId, 'key': k.keyPair.pubKey }));
        }

        bundle['preKeys'] = preKeys;

        ChatOmemo_ajaxAnnounceBundle(bundle);
    },

    ownDevicesReceived: async function (devices) {
        var store = new ChatOmemoStorage();
        const localDeviceId = await store.getLocalRegistrationId();

        if (!devices.includes(localDeviceId) && ChatOmemo.refreshed == false) {
            ChatOmemo.refreshBundle(true);
            ChatOmemo.refreshed = true;
        }
    },

    handlePreKeys: function (jid, preKeys) {
        let promises = [];

        Object.entries(preKeys).forEach(([deviceId, preKey]) => {
            // The prekey.jid is different from the jid when resolving a MUC
            promises.push(ChatOmemo.handlePreKey(preKey.jid, deviceId, preKey));
        });

        Promise.all(promises).then(results => {
            var store = new ChatOmemoStorage();

            /**
             * First time we handle a session, we enforce the OMEMO state to true
             */
            if (!store.hasContactState(jid)) {
                store.setContactState(jid, true);
            }

            if (!Chat) return;

            let textarea = Chat.getTextarea();
            if (textarea && textarea.dataset.jid == jid) {
                Chat.setOmemoState('yes');
                Chat.disableSending();

                if (Chat.getTextarea().value.length > 0) {
                    Chat.sendMessage();
                }
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

            if (!Chat) return;

            let textarea = Chat.getTextarea();
            if (textarea && textarea.dataset.jid == jid) {
                Chat.setOmemoState('yes');
            }
        });

        promise.catch(function onerror(error) {
            console.log(error);
        });

        return promise;
    },

    closeSessions: async function (jid, devicesIds) {
        var store = new ChatOmemoStorage();

        devicesIds.forEach(deviceId => {
            var address = new libsignal.SignalProtocolAddress(jid, deviceId);
            store.removeSession(address);
        })
    },

    encrypt: async function (to, plaintext, muc) {
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
        let ownKeys = await this.encryptJid(keyAndTag, store.jid);

        let remoteKeys = [];

        if (muc) {
            for (member of Chat.groupChatMembers) {
                remoteKeys = remoteKeys.concat(await this.encryptJid(keyAndTag, member));
            }
        } else {
            remoteKeys = await this.encryptJid(keyAndTag, to);
        }

        ownKeys = ownKeys.concat(remoteKeys);

        let messageKeys = {};
        ownKeys.map(result => {
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

        let resolvedId = message.mine
            ? message.originid
            : message.id;

        let maybeDecrypted = await ChatOmemoDB.getMessage(resolvedId);

        if (maybeDecrypted !== undefined) {
            return maybeDecrypted;
        }

        // Resolved jid from a muc message
        var jid = message.mucjid ?? message.jidfrom;

        // No keys
        if (message.omemoheader.keys == undefined) return;

        var store = new ChatOmemoStorage();
        let deviceId = await store.getLocalRegistrationId();
        let originalSessionsNumber = store.getSessions(jid).length;

        // We don't have any sessions from this message yet
        if (originalSessionsNumber == 0) {
            console.log('No sessions found for this contact, refresh the list: ' + jid);
            ChatOmemo.requestedDevicesListFrom = jid;
            ChatOmemo_ajaxGetDevicesList(jid);
            return;
        }

        if (message.omemoheader.keys[deviceId] == undefined) {
            console.log('Message not encrypted for this device');
            ChatOmemoDB.putMessage(resolvedId, false);
            return;
        }

        let key = message.omemoheader.keys[deviceId];
        let plainKey;

        try {
            plainKey = await this.decryptDevice(MovimUtils.base64ToArrayBuffer(key.payload), key.prekey, jid, message.omemoheader.sid);
        } catch (err) {
            ChatOmemoDB.putMessage(resolvedId, false);
            console.log('Error during decryption: ' + err);

            var address = new libsignal.SignalProtocolAddress(jid, message.omemoheader.sid);
            store.removeSession(address);
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
        if (store.getSessions(jid).length > originalSessionsNumber
        && Object.keys(message.omemoheader.keys).includes(String(deviceId))
        && ChatOmemo.requestedDevicesListFrom != jid) {
            console.log('A new session was created from the incoming message, refresh the contact devices for ' + jid);

            ChatOmemo.requestedDevicesListFrom = jid;
            ChatOmemo_ajaxGetDevicesList(jid);
        }

        ChatOmemoDB.putMessage(resolvedId, plaintext);
        return plaintext;
    },
    enableContactState: function (jid, muc) {
        var store = new ChatOmemoStorage();
        store.setContactState(jid, true);

        if (muc) {
            Chat_ajaxGetRoom(jid);
        } else {
            Chat_ajaxGet(jid);
        }

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
            .map(deviceId => store.getSessionState(jid + '.' + deviceId).then(state => {
                if (state) {
                    return this.encryptDevice(plaintext, jid, deviceId);
                }

                return Promise.resolve(false);
            }));

        return Promise.all(promises).then(result => {
            return result.filter(encrypted => encrypted !== false);
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
    },
    searchEncryptedFile: function(plaintext) {
        let lines = plaintext.split('\n');
        let matches = lines[0].match(AESGCM_REGEX);

        if (!matches) {
           return plaintext;
        }
        let [match, , filename, extension, hash] = matches;
        return '<i class="material-icons">file_download</i> <a href="#" class="encrypted_file" onclick="ChatOmemo.getEncryptedFile(\''
            + lines[0]
            + '\')">'
                + filename
            + '</a>';
    },
    getEncryptedFile: async function(encryptedUrl) {
        Chat.enableSending();

        let [, url, filename, , hash] = encryptedUrl.match(AESGCM_REGEX);
        url = 'https://' + url;
        let response;

        try {
            response = await fetch(url)
        } catch(e) {
            console.log('Cannot get the following file: ' + url)
            return null;
        }

        if (response.status >= 200 && response.status < 400) {
            let cipher = await response.arrayBuffer();
            const iv = hash.slice(0, 24);
            const key = hash.slice(24);

            const keyObj = await crypto.subtle.importKey('raw', MovimUtils.hexToArrayBuffer(key), 'AES-GCM', false, ['decrypt']);
            const algo = {
                'name': 'AES-GCM',
                'iv': MovimUtils.hexToArrayBuffer(iv),
            };
            let plainFile = await crypto.subtle.decrypt(algo, keyObj, cipher);

            const file = new File([plainFile], filename);
            const link = document.createElement('a');
            link.href = URL.createObjectURL(file);
            link.download = filename;
            link.click();

            Chat.disableSending();

            URL.revokeObjectURL(link.href)
        }
    }
}

MovimWebsocket.attach(function() {
    ChatOmemo.initGenerateBundle();
});