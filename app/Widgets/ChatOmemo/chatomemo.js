var KeyHelper = libsignal.KeyHelper;

var ChatOmemo = {
    refreshed: false,

    initiateBundle: async function (devicesIds) {
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
        ChatOmemo_ajaxAnnounceBundle(bundle, devicesIds);
        ChatOmemo_ajaxGetDevicesList(USER_JID);
    },

    refreshBundle: async function (devicesIds, publishOnly) {
        var store = new ChatOmemoStorage();

        const bundle = {};
        const localDeviceId = await store.getLocalRegistrationId();

        if (!localDeviceId) ChatOmemo.initiateBundle(devicesIds);

        // We get the base of the bundle from the store
        let keyPair = await store.getIdentityKeyPair();
        const deviceId = localDeviceId;

        bundle['identityKey'] = MovimUtils.arrayBufferToBase64(keyPair.pubKey);
        bundle['deviceId'] = deviceId;

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

        ChatOmemo_ajaxAnnounceBundle(bundle, devicesIds);
    },

    ownDevicesReceived: async function (from, devicesIds) {
        var store = new ChatOmemoStorage();
        const localDeviceId = await store.getLocalRegistrationId();
        devicesIds = Object.values(devicesIds);

        if (localDeviceId == undefined) {
            ChatOmemo.initiateBundle(devicesIds);
            return;
        }

        if (!devicesIds.includes(localDeviceId.toString()) && ChatOmemo.refreshed == false) {
            ChatOmemo.refreshBundle(devicesIds, true);
            ChatOmemo.refreshed = true;
        }
    },

    /**
     * A contact send us a new device list, we check if we can already build a new session
     */
    devicesReceived: async function (from, devicesIds) {
        var store = new ChatOmemoStorage();
        let localIds = store.getSessionsIds(from);

        if (await store.getContactState(from)) {
            for (deviceId of devicesIds.filter(key => !localIds.includes(key))) {
                ChatOmemo_ajaxGetBundle(from, deviceId);
            }

            /**
             * If we have open sessions from devices that doesn't exists anymore
             * we close them
             */
            for (deviceId of localIds.filter(key => !devicesIds.includes(key))) {
                var address = new libsignal.SignalProtocolAddress(from, deviceId);
                store.removeSession(address);
            }
        }
    },

    bundlesRefreshError(jid) {
        var store = new ChatOmemoStorage();
        store.setContactTombstone(jid);
    },

    bundlesRefreshed(jid) {
        var store = new ChatOmemoStorage();

        if (typeof Chat == 'undefined') return;

        let textarea = Chat && Chat.getTextarea();

        if (textarea) {
            // First time we handle a session, we enforce the OMEMO state to true by default
            if (!store.hasContactState(jid)) {
                store.setContactState(jid, true);
            }

            if (textarea.dataset.jid == jid) {
                Chat.setOmemoState('yes');
                Chat.disableSending();

                Chat_ajaxGet(jid);

                if (textarea.value.length > 0) {
                    Chat.sendMessage();
                }
            }
        }
    },

    handlePreKey: async function (jid, deviceId, preKey) {
        var store = new ChatOmemoStorage();

        const localDeviceId = await store.getLocalRegistrationId();
        if (localDeviceId == undefined) return;

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

            if (typeof Chat == 'undefined') return;

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
                payload: btoa(result.payload.body),
                prekey: 3 == parseInt(result.payload.type, 10)
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

        var store = new ChatOmemoStorage();

        let resolvedId = message.mine
            ? message.messageid
            : message.id;

        let maybeDecrypted = await ChatOmemoDB.getMessage(resolvedId);

        if (maybeDecrypted !== undefined) {
            return maybeDecrypted;
        }

        // Resolved jid from a muc message
        var jid = message.mucjid ?? message.jidfrom;

        // If there was an encryption tombstone on this contact we can lift it
        store.removeContactTombstone(jid);

        // No keys
        if (message.omemoheader.keys == undefined) return;

        let deviceId = await store.getLocalRegistrationId();

        /**
         * Check if we need to build more sessions
         */
        if (Chat) {
            textarea = Chat.getTextarea();

            if (!Boolean(textarea.dataset.muc)) {
                for (bundleId of Object.keys(message.omemoheader.keys).filter(
                    key => !store.getSessionsIds(jid)
                        .concat(store.getOwnSessionsIds(), [deviceId.toString()]).includes(key)
                )
                ) {
                    ChatOmemo_ajaxGetBundle(jid, bundleId);
                }
            }
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

            // Let the session heal, a new prekey will be send next time
            //var address = new libsignal.SignalProtocolAddress(jid, message.omemoheader.sid);
            //store.removeSession(address);
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
            ChatOmemo.refreshBundle(store.getOwnSessionsIds());
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

        ChatOmemoDB.putMessage(resolvedId, plaintext);
        return plaintext;
    },
    enableContactState: function (jid, muc) {
        var store = new ChatOmemoStorage();
        store.setContactState(jid, true);

        if (muc) {
            Chat_ajaxGetRoom(jid);
            ChatOmemo_ajaxEnableRoomState();

            Chat.groupChatMembers.forEach(member => {
                if (!store.isJidResolved(member)) {
                    ChatOmemo_ajaxGetDevicesList(member);
                }
            });
        } else {
            Chat_ajaxGet(jid);
            ChatOmemo_ajaxEnableContactState();
        }
    },
    disableContactState: function (jid, muc) {
        var store = new ChatOmemoStorage();
        store.setContactState(jid, false);
        Chat.setOmemoState("disabled");

        if (muc) {
            ChatOmemo_ajaxDisableRoomState();
        } else {
            ChatOmemo_ajaxDisableContactState();
        }
    },
    getContactState: async function (jid) {
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
    decryptDevice: async function (ciphertext, preKey, jid, deviceId) {
        var address = new libsignal.SignalProtocolAddress(jid, parseInt(deviceId, 10));
        var store = new ChatOmemoStorage();
        var sessionCipher = new libsignal.SessionCipher(store, address);

        return (preKey)
            ? await sessionCipher.decryptPreKeyWhisperMessage(ciphertext, 'binary')
            : await sessionCipher.decryptWhisperMessage(ciphertext, 'binary');
    },
    searchEncryptedFile: function (plaintext) {
        let lines = plaintext.split('\n');
        let matches = lines[0].match(AESGCM_REGEX);

        if (!matches) {
            return plaintext;
        }
        let [match, , filename, extension, hash] = matches;
        return '<i class="material-symbols">file_download</i> <a href="#" class="encrypted_file" onclick="ChatOmemo.getEncryptedFile(\''
            + lines[0]
            + '\')">'
            + filename
            + '</a>';
    },
    getEncryptedFile: async function (encryptedUrl) {
        Chat.enableSending();

        let [, url, filename, , hash] = encryptedUrl.match(AESGCM_REGEX);
        url = 'https://' + url;
        let response;

        try {
            response = await fetch(url)
        } catch (e) {
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
    },

    resolveContactFingerprints: function (jid) {
        let omemoStorage = new ChatOmemoStorage;

        let sessionLoaded = [];
        omemoStorage.getSessionsIds(jid).forEach(deviceId => {
            var address = new libsignal.SignalProtocolAddress(jid, deviceId);
            sessionLoaded.push(omemoStorage.loadSession(address.toString()));
        });

        const promise = new Promise((resolve) => {
            Promise.all(sessionLoaded).then(sessions => {
                let remoteKeys = [];

                sessions.forEach(session => {
                    let parsed = Object.values(JSON.parse(session).sessions)[0];
                    remoteKeys.push({
                        jid: jid,
                        self: false,
                        bundleid: parsed.registrationId.toString(),
                        fingerprint: btoa(parsed.indexInfo.remoteIdentityKey)
                    });
                });

                resolve(remoteKeys);
            });
        });

        return promise;
    }
}

MovimWebsocket.attach(async () => {
    var store = new ChatOmemoStorage();
    const localDeviceId = await store.getLocalRegistrationId();
    if (!localDeviceId) {
        ChatOmemo_ajaxGetDevicesList(USER_JID);
    }
});
