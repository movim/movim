var empty_count = 0;
var poller = rpc.make_xmlhttp();

/**
 * Polls the server.
 */
function movim_poll()
{
    poller = rpc.make_xmlhttp();
    poller.open('POST', BASE_URI+'jajax.php?do=poll', true);

    poller = rpc.set_key(poller);

    poller.addEventListener('loadstart', rpc.startRequest, false);
    poller.addEventListener('loadend', rpc.endRequest, false);

    poller.onreadystatechange = function()
    {
        if(poller.readyState == 4)
        {
            if(poller.status == 200) {
                // Handling poll return.
                if(poller.response == null) {
                    
                    if(empty_count == 3)
                        movim_disconnect('session');
                    else
                        empty_count++;
                } else {
                    empty_count = 0;
                    rpc.handle_rpc_json(poller.response);
                }
            } else if(poller.status == 500 || poller.status == 400) {            
                movim_disconnect('internal');
            }
            
            if(poller.status == 200) {
                // Restarting polling.
                movim_poll();
            }

        }
    };

    poller.send();
}

// Adding the polling to onload event.
movim_add_onload(movim_poll);
