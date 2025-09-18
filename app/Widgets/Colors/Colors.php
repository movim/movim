<?php

namespace App\Widgets\Colors;

use Movim\Widget\Base;

class Colors extends Base
{
    public function display()
    {
        header('Content-Type: text/css');
        $this->view->assign('colors', palette(true));
        $this->view->assign('accentcolor', $this->me->accentcolor ?? 'dorange');
    }
}
