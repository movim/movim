<?php

namespace Moxl\Xec\Action\OMEMO;

use App\Bundle;
use Moxl\Stanza\Disco;
use Moxl\Stanza\Pubsub;
use Moxl\Xec\Action;
use Moxl\Xec\Action\Pubsub\Delete;

class CleanDevicesList extends Action
{
    private array $_currentList;

    public function request()
    {
        $this->store();
        Disco::items();
    }

    public function setCurrentList(array $currentList)
    {
        $this->_currentList = $currentList;
        return $this;
    }

    public function handle(?\SimpleXMLElement $stanza = null, ?\SimpleXMLElement $parent = null)
    {
        $omemoItems = [];

        foreach ($stanza->query->item as $item) {
            if (str_starts_with((string)$item->attributes()->node, Bundle::OMEMO_BUNDLE)) {
                array_push ($omemoItems, substr((string)$item->attributes()->node, 39));
            }
        }

        foreach (array_diff($omemoItems, $this->_currentList) as $bundleId) {
            $delete = new Delete;
            $delete->setNode(Bundle::OMEMO_BUNDLE . $bundleId)
                ->request();
        }

        $this->deliver();
    }
}
