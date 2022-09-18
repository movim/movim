<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Location\Publish;

class Location extends Base
{
    public function load()
    {
        $this->addjs('location.js');
        $this->addcss('location.css');

        $this->registerEvent('mylocation', 'onMyLocation');
    }

    public function onMyLocation($packet)
    {
        $this->ajaxToggle();
        $this->rpc('MovimTpl.fill', '#location_widget', $this->prepareLocation());
    }

    public function ajaxPublish(float $latitude, float $longitude, int $accuracy)
    {
        if (isLatitude($latitude) && isLongitude($longitude)) {
            $geo = [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy' => $accuracy
            ];

            $p = new Publish;
            $p->setGeo($geo)->request();
        }
    }

    public function ajaxClear()
    {
        $p = new Publish;
        $p->setGeo([])->request();
    }

    public function ajaxToggle()
    {
        $view = $this->tpl();
        $view->assign('contact', $this->user->contact);

        Dialog::fill($view->draw('_location_toggle'));
    }

    public function ajaxHttpGet()
    {
        $html = $this->prepareLocation();
        if ($html) {
            $this->rpc('MovimTpl.fill', '#location_widget', $html);
        }
    }

    public function prepareLocation()
    {
        $view = $this->tpl();
        $view->assign('contact', $this->user->contact);

        return $view->draw('_location', true);
    }
}
