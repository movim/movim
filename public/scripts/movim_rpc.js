/**
 * Short functions
 */
function MWSa(widget, func, params) {
    return MovimRPC.ajax(widget, func, params, false);
}

function MWSad(widget, func, params) {
    return MovimRPC.ajax(widget, func, params, true);
}

var MovimRPC = {
    ajax : function(widget, func, params, daemon) {
        let xhr = new XMLHttpRequest;

        var body = {
            'w' : widget,
            'f' : func
        };

        if (params) body.p = params;

        var date = new Date();

        xhr.open('POST', daemon ? '?ajaxd': '?ajax');
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
        xhr.setRequestHeader('Movim-Offset', date.getTimezoneOffset());
        xhr.send(JSON.stringify(
            {'func' : 'message', 'b' : body }
        ));

        if (!daemon) {
            xhr.addEventListener('readystatechange', function(e) {
                if (this.readyState == 4 && this.status >= 200 && this.status < 400) {
                    var obj = JSON.parse(this.response);
                    for (funcall of obj) {
                        MovimRPC.handle(funcall);
                    }
                } else if (this.readyState == 4 && this.status == 403) {
                    MovimUtils.disconnect();
                }
            });
        }

        return xhr;
    },

    handle : function(funcall) {
        if (funcall.func != null && (typeof window[funcall.func] == 'function')) {
            try {
                window[funcall.func].apply(null, funcall.p);
            } catch(err) {
                console.log("Error caught: "
                    + err.toString()
                    + " - "
                    + funcall.func
                    + ":"
                    + JSON.stringify(funcall.p)
                );
            }
        } else if (funcall.func != null) {
            var funcs  = funcall.func.split('.');
            var called = funcs[0];
            if (typeof window[called] == 'object'
            && typeof window[funcs[0]][funcs[1]] != 'undefined') {
                window[funcs[0]][funcs[1]].apply(null, funcall.p);
            }
        }
    }
}