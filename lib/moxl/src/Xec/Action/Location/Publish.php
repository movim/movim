<?php

namespace Moxl\Xec\Action\Location;

use Moxl\Xec\Action;
use Moxl\Stanza\Location;

use App\Contact;

class Publish extends Action
{
    protected $_geo;

    public function request()
    {
        $this->store();
        Location::publish($this->_geo);
    }

    public function setGeo(array $geo)
    {
        $this->_geo  = $geo;
        return $this;
    }

    public function handle($stanza, $parent = false)
    {
        $from = baseJid((string)$stanza->attributes()->from);

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
}
