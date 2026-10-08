<?php

namespace Moxl\Xec\Action\Disco;

use Moxl\Xec\Action;
use Moxl\Stanza\Disco;

class Request extends Action
{
    protected ?string $_node = null;
    protected ?string $_to = null;
    protected ?string $_parent = null;

    public function request()
    {
        $this->store();
        $this->iq(Disco::request($this->_node), to: $this->_to, type: 'get');
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
                ($found->parent != null && $this->_parent == null)
                    ? $found->parent
                    : $this->_parent
            );
            $found->save();
            $info = $found;
        } else {
            $info->save();
        }

        $this->pack($info);
        $this->deliver();
    }
}
