<?php

namespace Moxl\Xec\Action\Presence;

use DOMElement;
use Moxl\Stanza\Presence;
use Moxl\Xec\Action;

class Muc extends Action
{
    public static $mucId = 'MUC_ID';

    protected $_to;
    protected $_nickname;
    protected $_create = false;
    protected $_mujiPreparing = false;
    protected ?DOMElement $_muji = null;
    protected ?string $_mavsince = null;

    // Disable the event
    protected $_notify = true;

    public function request()
    {
        $session = linker($this->sessionId)->session;

        if (empty($this->_nickname)) {
            $this->_nickname = linker($this->sessionId)->user->session->username;
        }

        $this->store(); // Set stanzaId

        /**
         * Some servers doesn't return the ID, so save it in another session key-value
         * and use the to and nickname as a key ¯\_(ツ)_/¯
         */
        $session->set(self::$mucId . $this->_to . '/' . $this->_nickname, $this->stanzaId);

        $this->send(Presence::maker(
            $this->me,
            to: $this->_to . '/' . $this->_nickname,
            muc: true,
            mujiPreparing: $this->_mujiPreparing,
            muji: $this->_muji,
            mavSince: $this->_mavsince
        ));
    }

    public function enableCreate()
    {
        $this->_create = true;
        return $this;
    }

    public function enableMujiPreparing()
    {
        $this->_mujiPreparing = true;
        return $this;
    }

    public function setMuji(DOMElement $muji)
    {
        $this->_muji = $muji;
        return $this;
    }

    public function noNotify()
    {
        $this->_notify = false;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $presence = \App\Presence::findByStanza($this->me, $stanza);
        $presence->set($this->me, $stanza);

        if ($stanza->attributes()->to) {
            $presence->mucjid = bareJid((string)$stanza->attributes()->to);
        }

        if ($this->_create) {
            $presence->save();

            if ($this->_mujiPreparing) {
                $this->method('create_muji_handle');
                $this->pack($presence);
                $this->deliver();
            }

            $this->method('create_handle');
            $this->pack($presence);
            $this->deliver();
        }

        linker($this->sessionId)->presenceBuffer->append($presence, function () use ($presence) {
            linker($this->sessionId)->chatroomPings->touch($presence->jid);

            if ($this->_mujiPreparing) {
                $this->method('muji_preparing');
                $this->deliver();
            }

            // XEP-0463: MUC Affiliations Versioning
            if ($presence->no_mav) {
                $this->method('no_mav_handle');
                $this->pack($presence);
                $this->deliver();
            }

            if ($this->_notify) {
                $this->method('handle'); // Reset
                $this->pack($presence);
                $this->deliver();
            }
        });
    }

    public function errorRegistrationRequired(string $errorId, ?string $message = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorRemoteServerNotFound(string $errorId, ?string $message = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorNotAuthorized(string $errorId, ?string $message = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorGone(string $errorId, ?string $message = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorNotAllowed(string $errorId, ?string $message = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorItemNotFound(string $errorId, ?string $message = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorJidMalformed(string $errorId, ?string $message = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorNotAcceptable(string $errorId, ?string $message = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorServiceUnavailable(string $errorId, ?string $message = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorForbidden(string $errorId, ?string $message = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorRemoteServerTimeout(string $errorId, ?string $message = null)
    {
        $this->pack($this->_to);
        $this->deliver();
    }

    public function errorConflict(string $errorId, ?string $message = null)
    {
        if (substr_count($this->_nickname, '_') > 5) {
            $this->deliver();
        } else {
            $this->setNickname($this->_nickname . '_');
            $this->request();
            $this->deliver();
        }
    }
}
