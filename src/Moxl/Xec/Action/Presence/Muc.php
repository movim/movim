<?php

namespace Moxl\Xec\Action\Presence;

use App\Presence as AppPresence;
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
    protected $_mujiLeaving = false;
    protected ?DOMElement $_muji = null;
    protected ?string $_mavsince = null;
    protected bool $_withVideo = false;

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

    public function enableMujiLeaving()
    {
        $this->_mujiLeaving = true;
        return $this;
    }

    public function setMuji(DOMElement $muji)
    {
        $this->_muji = $muji;
        return $this;
    }

    public function withVideo(bool $withVideo)
    {
        $this->_withVideo = $withVideo;
        return $this;
    }

    public function noNotify()
    {
        $this->_notify = false;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $presence = (new AppPresence);
        $presence->set($this->me, $stanza);

        if ($stanza->attributes()->to) {
            $presence->mucjid = bareJid((string)$stanza->attributes()->to);
        }

        if ($this->_create) {
            \App\Presence::where([
                'session_id' => $presence->session_id,
                'jid' => $presence->jid,
                'mucjid' => $presence->mucjid,
                'resource' => $presence->resource
            ])->delete();

            $presence->save();

            $this->method('create_handle');
            $this->pack($presence);
            $this->deliver();
        }

        linker($this->sessionId)->presenceBuffer->append($presence, function () use ($presence) {
            linker($this->sessionId)->chatroomPings->touch($presence->jid);

            if ($this->_mujiPreparing) {
                $this->method('muji_preparing');
                $this->pack(['with_video' => $this->_withVideo, 'presence' => $presence]);
                $this->deliver();
                return;
            }

            if ($this->_mujiLeaving) {
                $this->method('muji_leaving');
                $this->pack($presence);
                $this->deliver();
                return;
            }

            // XEP-0463: MUC Affiliations Versioning
            if ($presence->noMav) {
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
