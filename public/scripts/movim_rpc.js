/**
 * Short functions
 */
function MWSa(widget, func, params) {
    return MovimRPC.fetch(widget, func, params, false);
}

function MWSad(widget, func, params) {
    return MovimRPC.fetch(widget, func, params, true);
}

var MovimRPC = {
    fetchWithTimeout: async function (resource, options = {}) {
        const { timeout = 5000 } = options;

        const controller = new AbortController();

        const id = setTimeout(() => controller.abort(), timeout);
        const response = await fetch(resource, {
            ...options,
            signal: controller.signal
        });
        clearTimeout(id);

        return response;
    },

    fetch: function (widget, func, params, daemon) {
        var body = {
            'w': widget,
            'f': func
        };

        if (params) body.p = params;

        var date = new Date();

        let request = MovimRPC.fetchWithTimeout(BASE_URI + daemon ? '?ajaxd' : '?ajax', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Movim-Offset': date.getTimezoneOffset()
            },
            body: JSON.stringify(
                { 'func': 'message', 'b': body }
            )
        });

        if (!daemon) {
            request.then(response => {
                if (response.ok) {
                    return response.json();
                } else if (response.status == 403) {
                    MovimUtils.disconnect();
                }
            }).then(data => {
                for (funcall of data) {
                    MovimRPC.handle(funcall);
                }
            });
        }

        return request;
    },

    handle: function (funcall) {
        if (funcall.func != null && (typeof window[funcall.func] == 'function')) {
            try {
                window[funcall.func].apply(null, funcall.p);
            } catch (err) {
                console.log("Error caught: "
                    + err.toString()
                    + " - "
                    + funcall.func
                    + ":"
                    + JSON.stringify(funcall.p)
                );
            }
        } else if (funcall.func != null) {
            var funcs = funcall.func.split('.');
            var called = funcs[0];
            if (typeof window[called] == 'object'
                && typeof window[funcs[0]][funcs[1]] != 'undefined') {
                window[funcs[0]][funcs[1]].apply(null, funcall.p);
            }
        }
    }
}