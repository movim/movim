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

    abstract static public function getHandlerPath(): string;
    abstract public function handle(): ?\DOMDocument;

    public function iq(string $type, ?\DOMNode $xml = null): \DOMDocument
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $iq = $dom->createElementNS('jabber:client', 'iq');
        $dom->appendChild($iq);
        $iq->setAttribute('to', (string)$this->node->from);
        $iq->setAttribute('from', (string)$this->node->to);
        $iq->setAttribute('type', $type);
        $iq->setAttribute('id', $this->node->id);

        if ($xml != false) {
            $xml = $dom->importNode($xml, true);
            $iq->appendChild($xml);
        }

        return $dom;
    }
}
