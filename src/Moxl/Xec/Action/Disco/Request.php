<?php

namespace Moxl\Xec\Action\Disco;

use Moxl\Xec\Action;
use Moxl\Stanza\Disco;

class Request extends Action
{
    protected $_node = false;
    protected $_to;
    protected $_parent;

    // Excluded nodes
    protected $_excluded = [
        'http://www.android.com/gtalk/client/caps#1.1'
    ];

    public function request()
    {
        $this->store();

        if (!in_array($this->_node, $this->_excluded)) {
            Disco::request($this->_to, $this->_node);
        }
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        // Info
        $info = new \App\Info;
        $info->set($stanza, $this->_node, $this->_parent);

        /**
         * https://xmpp.org/extensions/xep-0390.html#rules-processing-caching
         */
        if (
            str_starts_with($info->node, 'urn:xmpp:caps')
            && !$info->checkCapabilityHash()
        ) return;

        $found = \App\Info::where('server', $info->server)
            ->where('node', $info->node)
            ->first();

        if ($found) {
            $found->set(
                $stanza,
                $this->_node,
                // If a parent was previously set, we keep it
                ($found->parent && !$this->_parent)
                    ? $found->parent
                    : $this->_parent
            );
            $found->save();
            $info = $found;
        } else {
            $info->save();
        }

        if (
            !$info->identities->contains('category', 'account')
            && !$info->identities->contains('category', 'client')
        ) {
            $this->pack($info);
            $this->deliver();
        }
    }
}
