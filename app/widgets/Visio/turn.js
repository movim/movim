var turnUrl = 'https://computeengineondemand.appspot.com/turn?username=93773443&key=4080218913';
var turnDone = false;

function maybeRequestTurn() {    
    if (turnUrl == '') {
        turnDone = true;
    }

    for (var i = 0, len = configuration.iceServers.length; i < len; i++) {
        if (configuration.iceServers[i].url.substr(0, 5) === 'turn:') {
            turnDone = true;
        }
    }

    var currentDomain = document.domain;
    if (currentDomain.search('localhost') === -1 &&
        currentDomain.search('apprtc') === -1) {
        // Not authorized domain. Try with default STUN instead.
        turnDone = true;
    }

    // No TURN server. Get one from computeengineondemand.appspot.com.
    xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = onTurnResult;
    xmlhttp.open('GET', turnUrl, true);
    xmlhttp.send();

    /*for (i = 0; i < VISIO_TURN_LIST.uris.length; i++) {
      var iceServer = createIceServer(VISIO_TURN_LIST.uris[i],
                                      VISIO_TURN_LIST.username,
                                      VISIO_TURN_LIST.password);
      if (iceServer !== null) {
        configuration.iceServers.push(iceServer);
      }
    }*/
}

function onTurnResult() {
    if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
        var turnServer = JSON.parse(xmlhttp.responseText);
        for (i = 0; i < turnServer.uris.length; i++) {
            // Create a turnUri using the polyfill (adapter.js).
            var iceServer = createIceServer(turnServer.uris[i],
                                          turnServer.username,
                                          turnServer.password);
            if (iceServer !== null) {
                configuration.iceServers.push(iceServer);
            }
        }
    } /*else {
        console.log(
            'Error: Status ' 
            + xmlhttp.status
            + ', State '
            + xmlhttp.readyState);
    }*/
    // If TURN request failed, continue the call with default STUN.
    turnDone = true;
}
