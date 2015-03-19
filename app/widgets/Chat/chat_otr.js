var ChatOTR = {
    buddy : null,
    load : function(jid) {
        var key = ChatOTR.getKey();

        var options = {
            fragment_size: 140,
            send_interval: 200,
            priv: key
        };

        ChatOTR.buddy = new OTR(options)

        ChatOTR.buddy.on('ui', function (msg, encrypted, meta) {
          console.log("message to display to the user: " + msg)
          //if(encrypted) {
              var message = document.querySelector('.pending');
              message.innerHTML = msg;
              message.className = '';

            ChatOTR.cleanPending();
          //}
          // encrypted === true, if the received msg was encrypted
          
          //console.log("(optional) with receiveMsg attached meta data: " + meta)
        });

        ChatOTR.buddy.on('io', function (msg, meta) {
          console.log("message to send to buddy: " + msg)
          Chat_ajaxSendMessage('demonstration@movim.eu', msg);
          console.log("(optional) with sendMsg attached meta data: " + meta)
        });

        ChatOTR.buddy.on('error', function (err, severity) {
          if (severity === 'error')  // either 'error' or 'warn'
            console.error("error occurred: " + err)
        });

        console.log(ChatOTR.buddy);
    },

    cleanPending : function() {
        var pending = document.querySelectorAll('.pending');

        var i = 0;
        while(i < pending.length)
        {
            pending[i].innerHTML = 'cant read';
            pending[i].className = '';
            i++;
        }
    },

    sendMessage : function(msg) {
        ChatOTR.buddy.sendMsg(msg);
    },

    getKey : function() {
        var key = localStorage.getObject('otr_key');
        if(!key) {
            key = new DSA();
            localStorage.setObject('otr_key', key);
        }
        var key = localStorage.getObject('otr_key');

        return DSA(key);
    },

    clear : function() {
        document.getElementById('demonstration@movim.eu_conversation').innerHTML = '';
    },

    decryptAll : function() {
        ChatOTR.cleanPending();

        var encrypted = document.querySelectorAll('.encrypted');

        var i = 0;
        while(i < encrypted.length)
        {
            var enc = encrypted[i].innerHTML;
            encrypted[i].className = 'pending';
            ChatOTR.buddy.receiveMsg(enc);
            i++;
        }
    }
}

ChatOTR.load('hop');
