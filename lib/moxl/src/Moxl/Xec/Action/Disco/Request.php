<?php

namespace Moxl\Xec\Action\Disco;

use Moxl\Xec\Action;
use Moxl\Stanza\Disco;
use Moxl\Xec\Action\Disco\Items;

class Request extends Action
{
    protected $_node;
    protected $_to;

    // Excluded nodes
    protected $_excluded = [
        'http://www.android.com/gtalk/client/caps#1.1'
    ];

    public function request()
    {
        $this->store();

        $info = \App\Info::where('server', $this->_to)
                         ->where('node', (string)$this->_node)
                         ->first();

        if (!in_array($this->_node, $this->_excluded)
        && (!$info || $info->isOld())) {
            Disco::request($this->_to, $this->_node);
        }
    }

    public function handle($stanza, $parent = false)
    {
        // Caps
        $capability = new \App\Capability;

        if (isset($this->_node)) {
            $capability->set($stanza, $this->_node);
        } else {
            $capability->set($stanza, $this->_to);
        }

        if ($capability->features != null
        && $capability->category != null) {
            $found = \App\Capability::find($capability->node);
            if ($found) $found->delete();

            $capability->save();
        }

        // Info
        $info = \App\Info::where('server', $this->_to)
                         ->where('node', (string)$this->_node)
                         ->first();

        if (!$info) $info = new \App\Info;
        $info->set($stanza);

        if (!empty($info->category)
        && $info->category !== 'account') {
            $info->save();
        }

        $this->pack([$this->_to, $this->_node]);
        $this->deliver();

        // Affiliations
        $affiliations = [];

        $owners = $stanza->query->xpath("//field[@var='pubsub#owner']/value/text()");
        if (!empty($owners)) {
            $affiliations['owner'] = [];
            foreach($owners as $owner) {
                array_push($affiliations['owner'], ['jid' => (string)$owner]);
            }
        }

        $publishers = $stanza->query->xpath("//field[@var='pubsub#publisher']/value/text()");
        if (!empty($publishers)) {
            $affiliations['publisher'] = [];
            foreach($publishers as $publisher) {
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
