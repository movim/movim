<?php

namespace Moxl\Xec\Action\Space;

use App\Conference;
use Moxl\Stanza\Avatar;
use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;
use Psr\Http\Message\ResponseInterface;

class GetRooms extends Action
{
    protected ?string $_to = null;
    protected ?string $_node = null;

    public function request()
    {
        $this->store();
        $this->iq(Pubsub::getItems($this->_node), to: $this->_to, type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $conferences = [];
        $conferenceIds = [];

        foreach ($stanza->pubsub->items->item as $c) {
            if ($c->conference && $c->conference->attributes()->xmlns == 'urn:xmpp:bookmarks:1') {
                $conference = new Conference;
                $conference->set($this->me->session, $c);
                $conference->space_server = $this->_to;
                $conference->space_node = $this->_node;
                array_push($conferences, $conference->toArray());
                array_push($conferenceIds, $conference->conference);
            }

            if (
                $c->attributes()->id == Avatar::NODE_METADATA
                && isset($c->metadata)
                && (string)$c->metadata->attributes()->xmlns == Avatar::NODE_METADATA
                && isset($c->metadata->info)
                && isset($c->metadata->info->attributes()->url)
            ) {
                requestAvatarUrl(
                    jid: $this->_to,
                    node: $this->_node,
                    url: (string)$c->metadata->info->attributes()->url
                )->then(function (ResponseInterface $response) {
                    $this->method('avatar');
                    $this->pack([
                        'server' => $this->_to,
                        'node' => $this->_node
                    ]);
                    $this->deliver();
                });
            }
        }

        $this->me->session
            ->conferences()
            ->whereIn('conference', $conferenceIds)
            ->delete();

        Conference::saveMany($conferences);

        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

    public function errorItemNotFound(string $errorId, ?string $message = null)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

    public function errorClosedNode(string $errorId, ?string $message = null)
    {
        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }
}
