;(function (root) {
  "use strict";

  root.OTR = {}
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
    , 'lib/sm.js'
  ]

  function wrapPostMessage(method) {
    return function () {
      postMessage({
          method: method
        , args: Array.prototype.slice.call(arguments, 0)
      })
    }
  }

  var sm
  onmessage = function (e) {
    var data = e.data
    switch (data.type) {
      case 'seed':
        if (data.imports) imports = data.imports
        importScripts.apply(root, imports)

        // use salsa20 since there's no prng in webworkers
        var state = new root.Salsa20(
          data.seed.slice(0, 32),
          data.seed.slice(32)
        )
        root.crypto.randomBytes = function (n) {
          return state.getBytes(n)
        }
        break
      case 'init':
        sm = new root.OTR.SM(data.reqs)
        ;['trust','question', 'send', 'abort'].forEach(function (m) {
          sm.on(m, wrapPostMessage(m));
        })
        break
      case 'method':
        sm[data.method].apply(sm, data.args)
        break
    }
  }

}(this))