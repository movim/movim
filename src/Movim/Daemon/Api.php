<?php

namespace Movim\Daemon;

use \React\Http\Server;
use \React\Socket\Server as Reactor;

class Api {
    private $_http;
    private $_core;

    public function __construct(Reactor $socket, Core $core)
    {
        $this->_core = &$core;
        $this->_http = new Server($socket);

        $api = &$this;

        $this->_http->on('request', function ($request, $response) use ($api) {
            $response->writeHead(200, array('Content-Type' => 'text/plain'));

            $url = explode('/', $request->getUrl()->getPath());

            switch($url[1]) {
                case 'exists':
                    $response->write($api->sessionExists($url[2]));
                    break;
                case 'disconnect';
                    $response->write($api->sessionDisconnect($url[2]));
                    break;
            }

            $response->end();
        });
    }

    public function sessionExists($sid)
    {
        $sessions = $this->_core->getSessions();
        return (array_key_exists($sid, $sessions)
        && $sessions[$sid] == true);
    }

    public function sessionDisconnect($sid)
    {
        return $this->_core->forceClose($sid);
    }
}

