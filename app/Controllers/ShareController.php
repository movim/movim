<?php

namespace App\Controllers;

use Movim\Controller\Base;

class ShareController extends Base
{
    public function dispatch()
    {
        $this->page->setTitle(__('page.share'));
    }
}
