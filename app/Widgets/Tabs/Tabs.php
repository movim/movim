<?php

namespace App\Widgets\Tabs;

use Movim\Widget\Base;

class Tabs extends Base
{
    public function load()
    {
        $this->addjs('tabs.js');
    }
}
