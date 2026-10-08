<?php

namespace Moxl\Xec\Action\Space;

use App\BookmarksDirectory;
use App\Conference;
use Moxl\Stanza\Avatar;
use Moxl\Stanza\Bookmark2;
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
        $this->iq(Pubsub::getItems($this->_node, 100), to: $this->_to, type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $conferences = [];
        $conferenceIds = [];

        foreach ($stanza->pubsub->items->item as $item) {
            if ($item->directories && $item->directories->attributes()->xmlns == Bookmark2::HIERARCHY_NAMESPACE) {
                $this->me->session->bookmarksDirectories()
                    ->where('server', $this->_to)
                    ->where('node', $this->_node)
                    ->delete();

                $i = 0;

                foreach ($item->directories->directory as $directory) {
                    $dir = new BookmarksDirectory;
                    $dir->session_id = $this->me->session->id;
                    $dir->server = $this->_to;
                    $dir->node = $this->_node;
                    $dir->id = (string) $directory->attributes()->id;
                    $dir->title = (string) $directory->attributes()->title;
                    $dir->order = $i;
                    $dir->save();
                    $i++;
                }
            }
        }

        foreach ($stanza->pubsub->items->item as $item) {
            if ($item->conference && $item->conference->attributes()->xmlns == Bookmark2::NODE . Bookmark2::VERSION) {
                $conference = new Conference;
                $conference->set(
                    $this->me->session,
                    item: $item,
                    spaceServer: $this->_to,
                    spaceNode: $this->_node,
                );

                array_push($conferences, $conference->toArray());
                array_push($conferenceIds, $conference->conference);
            }

            if (
                $item->attributes()->id == Avatar::NODE_METADATA
                && isset($item->metadata)
                && (string) $item->metadata->attributes()->xmlns == Avatar::NODE_METADATA
                && isset($item->metadata->info)
                && isset($item->metadata->info->attributes()->url)
            ) {
                requestAvatarUrl(
                    jid: $this->_to,
                    node: $this->_node,
                    url: (string) $item->metadata->info->attributes()->url
                )->then(function (ResponseInterface $response) {
                    $this->method('avatar');
                    $this->pack([
                        'server' => $this->_to,
                        'node' => $this->_node,
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
