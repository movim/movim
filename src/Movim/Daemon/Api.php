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
                    $response->write($api->sessionExists($request->getPost()));
                    break;
                case 'linked':
                    $response->write($api->sessionsLinked());
                    break;
                case 'started':
                    $response->write($api->sessionsStarted());
                    break;
                case 'unregister':
                    $response->write($api->sessionUnregister($request->getPost()));
                    break;
                case 'disconnect';
                    $response->write($api->sessionDisconnect($request->getPost()));
                    break;
                case 'purify':
                    $response->write($api->purifyHTML($request->getPost()));
                    break;
                case 'emojis':
                    $response->write($api->addEmojis($request->getPost()));
                    break;
                case 'session':
                    $response->write($api->getSession($request->getPost()));
                    break;
            }

            $response->end();
        });
    }

    public function sessionExists($post)
    {
        $sid = $post['sid'];

        $sessions = $this->_core->getSessions();
        return (array_key_exists($sid, $sessions)
        && $sessions[$sid] == true);
    }

    public function sessionsLinked()
    {
        return count($this->_core->getSessions());
    }

    public function getSession()
    {
        return count($this->_core->getSessions());
    }

    public function sessionsStarted()
    {
        $started = 0;
        foreach($this->_core->getSessions() as $s) {
            if($s == true) $started++;
        }
        return $started;
    }

    public function sessionUnregister($post)
    {
        $sid = $post['sid'];

        $session = $this->_core->getSession($sid);
        if($session) {
            $session->messageIn(json_encode(['func' => 'unregister']));
        }
    }

    public function sessionDisconnect($post)
    {
        $sid = $post['sid'];

        return $this->_core->forceClose($sid);
    }

    public function purifyHTML($post)
    {
        $string = urldecode($post['html']);

        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Doctype', 'XHTML 1.1');
        $config->set('Cache.SerializerPath', '/tmp');
        $config->set('HTML.DefinitionID', 'html5-definitions');
        $config->set('HTML.DefinitionRev', 1);
        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
              'src' => 'URI',
              'type' => 'Text',
              'width' => 'Length',
              'height' => 'Length',
              'poster' => 'URI',
              'preload' => 'Enum#auto,metadata,none',
              'controls' => 'Bool',
            ));
            $def->addElement('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
              'src' => 'URI',
              'preload' => 'Enum#auto,metadata,none',
              'muted' => 'Bool',
              'controls' => 'Bool',
            ));
            $def->addElement('source', 'Block', 'Flow', 'Common', array(
              'src' => 'URI',
              'type' => 'Text',
            ));
        }

        $purifier = new \HTMLPurifier($config);
        return trim($purifier->purify($string));
    }

    public function addEmojis($post)
    {
        $string = $post['string'];

        $emoji = \MovimEmoji::getInstance();
        return $emoji->replace($string);
    }
}

