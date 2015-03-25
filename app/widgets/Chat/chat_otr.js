var ChatOTR = {
    buddy : null,
    status : 0,
    load : function(jid) {
        var key = ChatOTR.getKey();

        var options = {
            fragment_size: 140,
            send_interval: 200,
            priv: key
        };

        ChatOTR.buddy = new OTR(options)

        ChatOTR.buddy.on('ui', function (msg, encrypted, meta) {
            console.log("!!! message to display to the user: " + msg)

            var message = {
                session : 'me',
                jidfrom : 'demonstration@movim.eu',
                jidto   : 'me',
                type    : 'chat',
                body    : msg
            };

            console.log(message);

            Chat.appendMessage(message);
        });

        ChatOTR.buddy.on('io', function (msg, meta) {
            console.log(">>> message to send to buddy: " + msg)
            Chat_ajaxSendMessage('demonstration@movim.eu', msg);
            //console.log("(optional) with sendMsg attached meta data: " + meta)
        });

        ChatOTR.buddy.on('error', function (err, severity) {
          if (severity === 'error')  // either 'error' or 'warn'
            console.error("error occurred: " + err)
        });

        ChatOTR.buddy.on('status', function (state) {
            switch (state) {
                case OTR.CONST.STATUS_AKE_SUCCESS:
                    movim_add_class(document.querySelector('#chat_header'), 'encrypted');
                    ChatOTR.status = 2;
                    break
                case OTR.CONST.STATUS_END_OTR:
                    movim_remove_class(document.querySelector('#chat_header'), 'encrypted');
                    ChatOTR.status = 0;
                    break
                }
        });

    },

    receiveMessage : function(enc) {
        console.log("<<< message received from the buddy: " + enc);
        if(ChatOTR.status == 0) {
            ChatOTR.buddy.sendQueryMsg();
            ChatOTR.status = 1;
        }
        ChatOTR.buddy.receiveMsg(enc);
    },

    sendMessage : function(msg) {
        if(ChatOTR.status == 0) {
            Chat_ajaxSendMessage('demonstration@movim.eu', msg);
        } else {
            ChatOTR.buddy.sendMsg(msg);
        }
    },

    getKey : function() {
        var key = localStorage.getObject('otr_key');
        if(!key) {
            key = new DSA();
            localStorage.setObject('otr_key', key);
        }
        var key = localStorage.getObject('otr_key');

        return DSA(key);
    }
}

ChatOTR.load('hop');
