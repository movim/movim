var ChatOmemoDB = {
    dbName: 'movim',
    storeName: 'decryptedMessages',
    version: 3,

    cachedMessages: {},

    db: null,

    setup: async function() {
        var db = window.indexedDB.open(this.dbName, this.version);
        db.onupgradeneeded = function (event) {
            var db = event.target.result;
            db.createObjectStore(ChatOmemoDB.storeName, { keyPath: "id" });
        }

        db.onsuccess = function() {
            ChatOmemoDB.db = db;
        }

        db.onerror = function() {
            console.log('Movim Decrypted Messages database cannot be opened properly');
        }
    },

    loadMessagesByIds: async function(ids) {
        ChatOmemoDB.cachedMessages = {};

        var tx = ChatOmemoDB.db.result.transaction(ChatOmemoDB.storeName, 'readonly');
        var os = tx.objectStore(ChatOmemoDB.storeName);

        var promises = [];

        ids.forEach(id => {
            var request = os.get(id);

            promises.push(new Promise((resolve, reject) => {
                request.onsuccess = function (event) {
                    if (event.target.result) {
                        ChatOmemoDB.cachedMessages[id] = event.target.result.body;
                    }
                    resolve();
                }

                request.onerror = function() {
                    resolve();
                }
            }));
        });

        return Promise.all(promises);
    },

    putMessage: async function(id, body) {
        let promise = new Promise(resolve => {
            var tx = ChatOmemoDB.db.result.transaction(ChatOmemoDB.storeName, 'readwrite');
            var os = tx.objectStore(ChatOmemoDB.storeName);

            var request = os.put({"id" : id, "body" : body});
            request.onsuccess = function(event) {
                resolve(body);
            }
        });

        return promise;
    },

    getMessage: async function(id) {
        let promise = new Promise(resolve => {
            if (id in ChatOmemoDB.cachedMessages) {
                resolve(ChatOmemoDB.cachedMessages[String(id)]);
            } else {
                var tx = ChatOmemoDB.db.result.transaction(ChatOmemoDB.storeName, 'readonly');
                var os = tx.objectStore(ChatOmemoDB.storeName);

                var request = os.get(id);
                request.onsuccess = function(event) {
                    if (event.target.result) {
                        resolve(event.target.result.body);
                    } else {
                        resolve();
                    }
                }
            }
        });

        return promise;
    }
}

ChatOmemoDB.setup();