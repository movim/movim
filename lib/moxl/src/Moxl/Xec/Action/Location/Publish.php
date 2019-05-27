<?php

namespace Moxl\Xec\Action\Location;

use Moxl\Xec\Action;
use Moxl\Stanza\Location;

class Publish extends Action
{
    protected $_to;
    protected $_geo;

    public function request()
    {
        $this->store();
        Location::publish($this->_to, $this->_geo);
    }

    public function handle($stanza, $parent = false)
    {
        $from = explodeJid((string)$stanza->attributes()->from)['jid'];

        /*$cd = new \modl\ContactDAO();
        $c = $cd->get($from);

        if($c == null) {
            $c = new \modl\Contact();
            $c->jid = $from;
        }

        $c->loclatitude = $this->_geo['latitude'];
        $c->loclongitude = $this->_geo['longitude'];
        $c->localtitude = $this->_geo['altitude'];
        $c->loccountry = $this->_geo['country'];
        $c->loccountrycode = $this->_geo['countrycode'];
        $c->locregion = $this->_geo['region'];
        $c->locpostalcode = $this->_geo['postalcode'];
        $c->loclocality = $this->_geo['locality'];
        $c->locstreet = $this->_geo['street'];
        $c->locbuilding = $this->_geo['building'];
        $c->loctext = $this->_geo['text'];
        $c->locuri = $this->_geo['uri'];
        $c->loctimestamp = date(
                            'Y-m-d H:i:s',
                            time());
        $cd->set($c);*/

        $this->event('locationpublished', $c);
    }

    public function errorServiceUnavailable($stanza)
    {
        $this->errorFeatureNotImplemented($stanza);
    }

    public function errorForbidden($stanza)
    {
        $this->errorNotAuthorized($stanza);
    }
}
