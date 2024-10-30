<?php
/*
 * SPDX-FileCopyrightText: 2024 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim;

use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Movim\Widget\Wrapper;
use SimpleXMLElement;

/**
 * This class handle the current Jitsi call
 */
class CurrentCall
{
    protected static $instance;
    public ?string $to = null;
    public ?string $id = null;
    public ?Carbon $startTime = null;

    private array $contents = [];

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function start(string $to, string $id)
    {
        $this->to = $to;
        $this->id = $id;
        $this->startTime = Carbon::now();

        $wrapper = Wrapper::getInstance();
        $wrapper->iterate('currentcall_started', [$this->getBareJid(), $id]);
    }

    public function stop()
    {
        $jid = $this->getBareJid();
        $id = $this->id;

        $this->to = $this->id = $this->startTime = null;

        $wrapper = Wrapper::getInstance();
        $wrapper->iterate('currentcall_stopped', [$jid, $id]);
    }

    public function isJidInCall(string $jid): bool
    {
        return $jid == $this->getBareJid();
    }

    public function isStarted(): bool
    {
        return $this->to != null && $this->id != null;
    }

    public function getBareJid(): ?string
    {
        if (!$this->isStarted()) return null;

        return explodeJid($this->to)['jid'];
    }

    /**
     * Content management
     */
    public function setContent(SimpleXMLElement $jingleStanza): SimpleXMLElement
    {
        if ($jingleStanza->attributes()->sid == $this->id) {
            $contentIds = [];

            if ($jingleStanza->group && $jingleStanza->group->attributes()->xmlns == 'urn:xmpp:jingle:apps:grouping:0') {
                foreach ($jingleStanza->xpath('//content/@name') as $contentId) {
                    array_push($contentIds, 'c' . (string)$contentId);
                }

                foreach (array_diff(array_keys($this->contents, $contentIds)) as $removedContentId) {
                    unset($this->contents['c' . $removedContentId]);
                }
            } else {
                // We only have one content without grouping
                array_push($contentIds, 'c' . (string)$jingleStanza->xpath('//content/@name')[0]);
            }

            foreach ($jingleStanza->content as $content) {
                $contentId = 'c' . (string)$content->attributes()->name;

                if (in_array($contentId, $contentIds)) {
                    $this->contents[$contentId] = $content->asXML();
                }
            }

            // Clear
            $elementsToRemove = [];
            foreach ($jingleStanza->content as $content) {
                $elementsToRemove[] = $content;
            }

            foreach ($elementsToRemove as $content) {
                unset($content[0]);
            }

            $domJingle = new DOMDocument();
            $domJingle->loadXML($jingleStanza->asXML());

            foreach ($this->contents as $key => $content) {
                $fragment = $domJingle->createDocumentFragment();
                $fragment->appendXML($content);

                $domJingle->documentElement->appendChild($fragment);
            }

            $domXML = $domJingle->saveXML($domJingle->documentElement);

            // Cleaning up the xmlns:default bug, see https://bugs.php.net/bug.php?id=47530
            $domXML = preg_replace('/xmlns:default\d?="[^"]*" /', '', $domXML);
            $domXML = preg_replace('/default[0-9]?:/', '', $domXML);

            $jingleStanza = simplexml_load_string($domXML);

            return $jingleStanza;
        }

        return $jingleStanza;
    }
}
