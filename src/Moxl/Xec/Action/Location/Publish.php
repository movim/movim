<?php

namespace Moxl\Xec\Action\Location;

use Moxl\Xec\Action;
use Moxl\Stanza\Location;

use App\Contact;
use Moxl\Xec\Action\Pubsub\SetConfig;

class Publish extends Action
{
    protected $_geo;
    protected bool $_withPublishOption = true;

    public function request()
    {
        $this->store();
        Location::publish($this->_geo, $this->_withPublishOption);
    }

    public function setGeo(array $geo)
    {
        $this->_geo  = $geo;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $from = bareJid((string)$stanza->attributes()->from);

        $contact = Contact::firstOrNew(['id' => $from]);

        if (empty($this->_geo)) {
            $contact->loclatitude = $contact->loclongitude = $contact->loctimestamp = null;
        } else {
            $contact->loclatitude      = $this->_geo['latitude'];
            $contact->loclongitude     = $this->_geo['longitude'];
            $contact->loctimestamp     = date('Y-m-d H:i:s');
        }

        $contact->save();

        $this->deliver();
    }

    public function errorResourceConstraint(string $errorId, ?string $message = null)
    {
        $this->errorConflict($errorId, $message);
    }

    public function errorConflict(string $errorId, ?string $message = null)
    {
        $config = new SetConfig;
        $config->setNode(Location::$node)
               ->setData(Location::$nodeConfig)
               ->request();

        $this->_withPublishOption = false;
        $this->request();
    }
}
