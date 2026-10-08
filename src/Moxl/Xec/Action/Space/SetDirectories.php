<?php

namespace Moxl\Xec\Action\Space;

use App\BookmarksDirectory;
use Illuminate\Support\Collection;
use Moxl\Stanza\Bookmark2;
use Moxl\Stanza\Space;
use Moxl\Xec\Action;
use Moxl\Xec\Action\Pubsub\SetConfig;

class SetDirectories extends Action
{
    protected ?Collection $_directories;

    protected string $_to;

    protected string $_node;

    protected bool $_withPublishOption = true;

    public function request()
    {
        $this->store();
        $this->iq(Bookmark2::setDirectories(
            directories: $this->_directories,
            node: $this->_node,
            withPublishOption: $this->_withPublishOption,
            nodeConfig: Space::NODE_CONFIG,
        ), to: $this->_to, type: 'set');
    }

    public function setDirectories(Collection $directories)
    {
        $this->_directories = $directories;

        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $currentDirectories = $this->me->session->bookmarksDirectories()
            ->where('server', $this->_to)
            ->where('node', $this->_node)
            ->pluck('title', 'id');

        $i = 0;

        $this->me->session->bookmarksDirectories()
            ->where('server', $this->_to)
            ->where('node', $this->_node)
            ->whereNotIn('id', $this->_directories->keys())
            ->delete();

        foreach ($this->_directories as $id => $title) {
            if ($currentDirectories->has($id)) {
                if ($currentDirectories->get($id) != $title) {
                    $this->me->session->bookmarksDirectories()
                        ->where('server', $this->_to)
                        ->where('node', $this->_node)
                        ->where('id', $id)
                        ->update(['title' => $title, 'order' => $i]);
                }

                $this->_directories->forget($id);
            } else {
                $directory = new BookmarksDirectory;
                $directory->session_id = $this->me->session->id;
                $directory->server = $this->_to;
                $directory->node = $this->_node;
                $directory->id = $id;
                $directory->title = $title;
                $directory->order = $i;
                $directory->save();
                $i++;
            }

            $i++;
        }

        $this->pack(['server' => $this->_to, 'node' => $this->_node]);
        $this->deliver();
    }

    public function errorPreconditionNotMet(string $errorId, ?string $message = null)
    {
        $this->errorConflict($errorId, $message);
    }

    public function errorResourceConstraint(string $errorId, ?string $message = null)
    {
        $this->errorConflict($errorId, $message);
    }

    public function errorConflict(string $errorId, ?string $message = null)
    {
        $config = new SetConfig($this->me, sessionId: $this->sessionId);
        $config->setTo($this->_to)
            ->setNode($this->_node)
            ->setData(Space::NODE_CONFIG)
            ->request();

        $this->_withPublishOption = false;
        $this->request();
    }
}
