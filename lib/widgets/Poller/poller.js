var last_poll;
var poll_flood_detect = 3;
var failed_poll_count = 0;

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
    last_poll = new Date().getTime();
    
	poller = MovimRPC_make_xmlhttp();
	poller.open('GET', 'jajax.php?do=poll', true);

	poller.onreadystatechange = function()
	{
		if(poller.readyState == 4)
		{
			if(poller.status == 200) {
				// Handling poll return.
                MovimRPC_handle_rpc(poller.responseXML);
            }

			if(poller.status > 0) {
                var time = new Date().getTime();
                if(time - last_poll < 300) {
                    failed_poll_count++;
                    if(failed_poll_count > poll_flood_detect) {
                        window.location = "index.php?q=disconnect";
                        return;
                    }
                } else {
                    failed_poll_count = 0;
                }
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
