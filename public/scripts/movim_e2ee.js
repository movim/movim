const KEY_ALGO = {
    'name': 'AES-GCM',
    'length': 128
};
const NUM_PREKEYS = 50;
const SIGNED_PREKEY_ID = 1;
const AESGCM_REGEX = /^aesgcm:\/\/([^#]+\/([^\/]+\.([a-z0-9]+)))#([a-z0-9]+)/i;
