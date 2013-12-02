var empty_count = 0;

/**
 * Attach a callback function to an event.
 */
function movimRegisterPollHandler(type, func)
{
	if(!(type in movimPollHandlers)) {
		movimPollHandlers[type] = new Array();
	}
	movimPollHandlers[type].push(func);
}

/**
 * Polls the server.
 */
function movim_poll()
{
	poller = rpc.make_xmlhttp();
	poller.open('POST', BASE_URI+'jajax.php?do=poll', true);

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

function halt_poll()
{
	poller.abort();
}

// Adding the polling to onload event.
movim_add_onload(movim_poll);
