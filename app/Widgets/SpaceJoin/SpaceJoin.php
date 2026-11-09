<?php

namespace App\Widgets\SpaceJoin;

use App\Info;
use Movim\Widget\Base;

class SpaceJoin extends Base
{
    private Info $space;
    public function load()
    {
        $this->space = Info::where('server', $this->get('s'))
            ->where('node', $this->get('n'))
            ->first();

        if ($this->space) {
            $this->title = $this->space->name;
            $this->description = $this->space->description;
            $this->image = $this->space->getPicture(placeholder: $this->space->name);
        }
    }

    public function display()
    {
        $this->view->assign('icon', $this->image);
        $this->view->assign('space', $this->space);
        $this->view->assign('loginUrl', $this->route('login', null, [
            'redirect' => $this->route('space', [
                $this->space->server,
                $this->space->node
            ])
        ]));
    }
}
