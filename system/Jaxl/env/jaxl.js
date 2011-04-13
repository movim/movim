/** 
 * Jaxl (Jabber XMPP Library)
 *
 * Copyright (c) 2009-2010, Abhinav Singh <me@abhinavsingh.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Abhinav Singh nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 * 
 * @package jaxl
 * @subpackage env
 * @author Abhinav Singh <me@abhinavsingh.com>
 * @copyright Abhinav Singh
 * @link http://code.google.com/p/jaxl
 */
function call_user_func(cb) {
    var func;
    if(typeof cb === 'string') func = (typeof this[cb] === 'function') ? this[cb] : func = (new Function(null, 'return ' + cb))();
    else if(cb instanceof Array) func = ( typeof cb[0] == 'string' ) ? eval(cb[0]+"['"+cb[1]+"']") : func = cb[0][cb[1]];
    else if (typeof cb === 'function') func = cb;
    if(typeof func != 'function') throw new Error(func + ' is not a valid function');   
    var parameters = Array.prototype.slice.call(arguments, 1);
    return (typeof cb[0] === 'string') ? func.apply(eval(cb[0]), parameters) : (typeof cb[0] !== 'object') ? func.apply(null, parameters) : func.apply(cb[0], parameters);
}

var jaxl = {
    jid: false,
    polling: 0,
    pollUrl: false,
    lastPoll: false,
    pollRate: 500,
    now: false,
    connected: false,
    disconnecting: false,
    payloadHandler: false,
    connect: function(obj) {
        if(obj == null) obj = new Object;
        obj['jaxl'] = 'connect';
        jaxl.sendPayload(obj);
    },
    disconnect: function(obj) {
        if(obj == null) obj = new Object;
        obj['jaxl'] = 'disconnect';
        jaxl.sendPayload(obj);
    },
    ping: function(obj) {
        if(obj == null) obj = new Object;
        obj['jaxl'] = 'ping';
        jaxl.sendPayload(obj);
    },
    preparePayload: function(obj) {
        var json = "{";
        for(key in obj) {
            if(json == "{") json += "'"+key+"':'"+obj[key]+"'";
            else json += ", '"+key+"':'"+obj[key]+"'";
        }
        json += "}";
        return eval("("+json+")");
    },
    sendPayload: function(obj) {
        jaxl.now = new Date().getTime();
        if(jaxl.lastPoll == false) {
            jaxl.xhrPayload(obj);
        }
        else {
            diff = jaxl.now-jaxl.lastPoll;
            
            if(diff < jaxl.pollRate) {
                var xhr = function() { jaxl.xhrPayload(obj); };
                
                // TO-DO: Use a queue instead
                setTimeout(xhr, jaxl.pollRate);
            }
            else {
                jaxl.xhrPayload(obj);
            }
        }
    },
    xhrPayload: function(obj) {
        if((jaxl.polling != 0 || !jaxl.connected || jaxl.disconnecting) && obj['jaxl'] == 'ping') return false;
        
        $.ajax({
            type: 'POST',
            url: jaxl.pollUrl,
            dataType: 'json',
            data: jaxl.preparePayload(obj),
            beforeSend: function() {
                jaxl.lastPoll = new Date().getTime();
                if(obj['jaxl'] == 'disconnect') {
                    jaxl.disconnecting = true;
                }
                jaxl.polling++;
            },
            success: function(payload) {
                jaxl.polling--;
                jaxl.handlePayload(payload);
            },
            complete: function() {},
            error: function() { jaxl.polling--; }
        });
    },
    handlePayload: function(payload) {
        if(payload.length == 0) { jaxl.ping(); }
        else { 
            for(key in payload) {
                if(key == null) { jaxl.ping(); }
                else if(payload[key].jaxl == 'jaxl') { jaxl.xhrPayload(payload[key]); }
                else { call_user_func(jaxl.payloadHandler, payload[key]); }
            }
        }
    },
    urldecode: function(msg) {
        return decodeURIComponent(msg.replace(/\+/g, '%20'));
    },
    urlencode: function(msg) {
        return encodeURIComponent(msg);
    },
    htmlEntities: function(msg) {
        return msg.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    },
    stripHTML: function(msg) {
        return msg.replace(/<\/?[^>]+>/gi,'');
    },
    splitJid: function(jid) {
        part1=jid.split("@");
        part2=part1[1].split("/");
        ret=new Object;
        ret['jid']=part1[0];
        ret['domain']=part2[0];
        ret['res']=part2[1];
        return ret;
    }
};
