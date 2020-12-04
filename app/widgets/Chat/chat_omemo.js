var KeyHelper = libsignal.KeyHelper;

var registrationId = KeyHelper.generateRegistrationId();
// Store registrationId somewhere durable and safe.
console.log(registrationId);

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
});