<?php

namespace App\Widgets\PublishHelp;

use App\Draft;
use App\Widgets\Drawer\Drawer;
use App\Widgets\Publish\Publish;
use Movim\Widget\Base;

class PublishHelp extends Base
{
    public function load()
    {
    }

    public function ajaxDrawer()
    {
        $view = $this->tpl();
        $this->drawer('publish_help', $view->draw('_publishhelp'), true);
    }

    public function prepareToggles(Draft $draft)
    {
        return (new Publish($this->me, sessionId: $this->sessionId))->prepareToggles($draft);
    }

    public function prepareHelp()
    {
        $view = $this->tpl();
        return $view->draw('_publishhelp');
    }
}
