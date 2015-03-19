;(function (root) {
  "use strict";

  root.OTR = {}
  root.DSA = {}
  root.crypto = {
    randomBytes: function () {
      throw new Error("Haven't seeded yet.")
    }
  }

  // default imports
  var imports = [
      'vendor/salsa20.js'
    , 'vendor/bigint.js'
    , 'vendor/crypto.js'
    , 'vendor/eventemitter.js'
    , 'lib/const.js'
    , 'lib/helpers.js'
    , 'lib/dsa.js'
  ]

  function sendMsg(type, val) {
    postMessage({ type: type, val: val })
  }

  onmessage = function (e) {
    var data = e.data;

    if (data.imports) imports = data.imports
    importScripts.apply(root, imports);

    // use salsa20 since there's no prng in webworkers
    var state = new root.Salsa20(data.seed.slice(0, 32), data.seed.slice(32))
    root.crypto.randomBytes = function (n) {
      return state.getBytes(n)
    }

    if (data.debug) sendMsg('debug', 'DSA key creation started')
    var dsa
    try {
      dsa = new root.DSA()
    } catch (e) {
      if (data.debug) sendMsg('debug', e.toString())
      return
    }
    if (data.debug) sendMsg('debug', 'DSA key creation finished')

    sendMsg('data', dsa.packPrivate())
  }

}(this))