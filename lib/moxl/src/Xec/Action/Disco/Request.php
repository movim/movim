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

    public function handle($stanza, $parent = false)
    {
        $this->pack([$this->_to, $this->_node]);

        // Info
        $info = new \App\Info;
        $info->set($stanza, $this->_node, $this->_parent);

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

        if (!$info->identities->contains('category', 'account')
        && !$info->identities->contains('category', 'client')) {
            $this->deliver();
        }

        // Affiliations
        $affiliations = [];

        $owners = $stanza->query->xpath("//field[@var='pubsub#owner']/value/text()");
        if (!empty($owners)) {
            $affiliations['owner'] = [];
            foreach ($owners as $owner) {
                array_push($affiliations['owner'], ['jid' => (string)$owner]);
            }
        }

        $publishers = $stanza->query->xpath("//field[@var='pubsub#publisher']/value/text()");
        if (!empty($publishers)) {
            $affiliations['publisher'] = [];
            foreach ($publishers as $publisher) {
                array_push($affiliations['publisher'], ['jid' => (string)$publisher]);
            }
        }

        if (!empty($affiliations)) {
            $this->pack([
                'affiliations' => $affiliations,
                'server' => $this->_to,
                'node' => $this->_node
            ]);
            $this->method('affiliations');
            $this->deliver();
        }
    }
}
