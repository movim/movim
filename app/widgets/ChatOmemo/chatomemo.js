var KeyHelper = libsignal.KeyHelper;
/*
var deviceId = KeyHelper.generateRegistrationId();
// Store deviceId somewhere durable and safe.
console.log(deviceId);

KeyHelper.generateIdentityKeyPair().then(function(identityKeyPair) {
    console.log(identityKeyPair);
    // keyPair -> { pubKey: ArrayBuffer, privKey: ArrayBuffer }
    // Store identityKeyPair somewhere durable and safe.
});

KeyHelper.generatePreKey(1).then(function(preKey) {
    console.log('preKey');
    console.log(preKey);
    store.storePreKey(preKey.keyId, preKey.keyPair);
});

KeyHelper.generateSignedPreKey(identityKeyPair, keyId).then(function(signedPreKey) {
    store.storeSignedPreKey(signedPreKey.keyId, signedPreKey.keyPair);
});*/

var ChatOmemo = {
    generateBundle : async function()
    {
        const identityKeyPair = await KeyHelper.generateIdentityKeyPair();
        const bundle = {};
        const identityKey = MovimUtils.arrayBufferToBase64(identityKeyPair.pubKey);
        const deviceId = KeyHelper.generateRegistrationId();

        bundle['identityKey'] = identityKey;
        bundle['deviceId'] = deviceId;
        /*this.save({
            'deviceId': deviceId,
            'identityKeyPair': {
                'privKey': MovimUtils.arrayBufferToBase64(identityKeyPair.privKey),
                'pubKey': identityKey
            },
            'identityKey': identityKey
        });*/
        const signedPreKey = await KeyHelper.generateSignedPreKey(identityKeyPair, 0);
        console.log(signedPreKey);
        localStorage.setObject('signedPreKey', {
            'privKey' : MovimUtils.arrayBufferToBase64(signedPreKey.keyPair.privKey),
            'pubKey' : MovimUtils.arrayBufferToBase64(signedPreKey.keyPair.pubKey),
            'signature' : MovimUtils.arrayBufferToBase64(signedPreKey.signature),
        });

        //_converse.omemo_store.storeSignedPreKey(signedPreKey);
        bundle['signedPreKey'] = {
            'id': signedPreKey.keyId,
            'publicKey': MovimUtils.arrayBufferToBase64(signedPreKey.keyPair.privKey),
            'signature': MovimUtils.arrayBufferToBase64(signedPreKey.signature)
        }
        const keys = await Promise.all(MovimUtils.range(0, 5).map(id => KeyHelper.generatePreKey(id)));
        //keys.forEach(k => _converse.omemo_store.storePreKey(k.keyId, k.keyPair));
        keys.forEach(k => {
            localStorage.setObject('preKey' + k.keyId, {
                'privKey' : MovimUtils.arrayBufferToBase64(k.keyPair.privKey),
                'pubKey' : MovimUtils.arrayBufferToBase64(k.keyPair.pubKey),
            });
        });
        const preKeys = keys.map(k => ({'id': k.keyId, 'key': MovimUtils.arrayBufferToBase64(k.keyPair.pubKey)}));
        bundle['preKeys'] = preKeys;

        ChatOmemo_ajaxAnnounceBundle(bundle);
        /*const devicelist = _converse.devicelists.get(_converse.bare_jid);
        const device = await devicelist.devices.create({'id': bundle.deviceId, 'jid': _converse.bare_jid}, {'promise': true});
        const marshalled_keys = keys.map(k => ({'id': k.keyId, 'key': MovimUtils.arrayBufferToBase64(k.keyPair.pubKey)}));
        bundle['prekeys'] = marshalled_keys;
        device.save('bundle', bundle);*/
    }
}