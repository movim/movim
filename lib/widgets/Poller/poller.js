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
	poller = makeXMLHttpRequest();
	poller.open('GET', 'jajax.php?do=poll', true);

	poller.onreadystatechange = function()
	{
		if(poller.readyState == 4)
		{
			if(poller.status == 200) {
				// Handling poll return.
                var movimreturn = poller.responseXML;
                try {
                    if(movimreturn != null) {
                        var movimtags = movimreturn.getElementsByTagName("movim");
                        for(h = 0; h < movimtags.length; h++) {
                            var widgetreturn = movimtags[h];
                            var target = widgetreturn.getElementsByTagName("target")[0].childNodes[0].textContent;
                            var method = widgetreturn.getElementsByTagName("target")[0].attributes.getNamedItem("method").nodeValue;
                            var payload = widgetreturn.getElementsByTagName("payload")[0].childNodes[0].nodeValue;

                            if(method == 'APPEND') {
				                document.getElementById(target).innerHTML += payload;
	                        }
	                        else if(method == 'PREPEND') {
				                var elt = document.getElementById(target);
				                elt.innerHTML = payload + elt.innerHTML;
			                }
                            else if(method == 'DROP') {
                                // Do nothing.
                            }
                            else { // Default is FILL.
				                document.getElementById(target).innerHTML = payload;
	                        }
                        }
                    }
                }
                catch(err) {
                    log("Error caught: " + err.toString());
                }
            }

			if(poller.status > 0) {
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