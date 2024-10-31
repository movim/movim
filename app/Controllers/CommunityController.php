<?php

namespace App\Controllers;

use Movim\Controller\Base;

class CommunityController extends Base
{
    public function dispatch()
    {
        $this->page->setTitle(__('page.explore'));
    }
}
