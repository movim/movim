<?php

namespace Moxl\Xec\Action\Version;

use App\Version as AppVersion;
use Moxl\Stanza\Version;
use Moxl\Xec\Action;

class Get extends Action
{
    protected string $_to;

    public function request()
    {
        $this->store();
        $this->iq(Version::get(), to: $this->_to, type: 'get');
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        if ($stanza->query && $stanza->query->attributes()->xmlns == 'jabber:iq:version') {
            $version = AppVersion::firstOrNew(['id' => $this->_to]);
            $version->name = $stanza->query->name;
            $version->version = $stanza->query->version;
            $version->os = $stanza->query->os;
            $version->save();

            $this->pack($version);
            $this->deliver();
        }
    }
}
