<?php

namespace App\Workers\Galener;

use SimpleXMLElement;

class XMPPHandler
{
    private array $paths = [];

    public function __construct(
        private GaleneAPIClient $apiClient,
        private ConferencesManager $conferencesManager
    ) {
        $directory = dirname(__FILE__);

        foreach (array_diff(scandir($directory . '/Events'), ['..', '.', 'Event.php']) as $eventFile) {
            $classname = substr($eventFile, 0, -4);
            $eventPath = 'App\\Workers\\Galener\\Events\\' . $classname;
            $this->paths[$eventPath::getHandlerPath()] = $eventPath;
        }
    }

    public function handle(SimpleXMLElement $node): ?\DOMDocument
    {
        $path = $node->getName();
        \logDebug('GET <<<<<< ' . $node->asXML());

        if ($child = $node->children()[0]) {
            $path .= '|' . $child->getName();

            if ($childNamespace = $child->attributes()->{'xmlns'} ?? null) {
                $path .= '{' . $childNamespace . '}';
            }
            if ($childNode = $child->attributes()->{'node'} ?? null) {
                $path .= '@' . $childNode;
            }
        }

        if (array_key_exists($path, $this->paths)) {
            $xmppNode = new XMPPNode($node);
            $event = new $this->paths[$path](
                node: $xmppNode,
                apiClient: $this->apiClient,
                conferencesManager: $this->conferencesManager
            );
            return $event->handle();
        }

        return null;
    }
}
