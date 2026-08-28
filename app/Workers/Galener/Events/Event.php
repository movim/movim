<?php

namespace App\Workers\Galener\Events;

use App\Workers\Galener\ConferencesManager;
use App\Workers\Galener\GaleneAPIClient;
use App\Workers\Galener\XMPPNode;

abstract class Event
{
    public function __construct(
        protected XMPPNode $node,
        protected GaleneAPIClient $apiClient,
        protected ConferencesManager $conferencesManager
    ) {}

    abstract static public function getHandlerPaths(): array;
    abstract public function handle(): ?\DOMDocument;

    public function iq(
        string $type,
        ?\DOMNode $xml = null,
        ?string $error = null,
        ?string $from = null,
        ?string $to = null
    ): \DOMDocument {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $iq = $dom->createElementNS('jabber:client', 'iq');
        $dom->appendChild($iq);
        $iq->setAttribute('to', $to ?? (string)$this->node->from);
        $iq->setAttribute('from', $from ?? (string)$this->node->to);
        $iq->setAttribute('type', $type);
        $iq->setAttribute('id', $this->node->id ?? \generateKey());

        if ($xml != false) {
            $xml = $dom->importNode($xml, true);
            $iq->appendChild($xml);
        }

        if ($type == 'error' && !empty($error)) {
            $errorElement = $dom->createElement('error');
            $errorElement->setAttribute('type', 'cancel');
            $iq->appendChild($errorElement);

            $errorTypeElement = $dom->createElement($error);
            $errorTypeElement->setAttribute('xmlns', 'urn:ietf:params:xml:ns:xmpp-stanzas');
            $errorElement->appendChild($errorTypeElement);
        }

        return $dom;
    }
}
