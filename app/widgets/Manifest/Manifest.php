<?php

use Movim\Widget\Base;

use App\Configuration;
use App\User;

class Manifest extends Base
{
    public function display()
    {
        $infos = [
            'name'          => APP_TITLE,
            'short_name'    => APP_TITLE,
            'description'   => __('global.description'),
            'icons'         => [
                [
                    'src' => BASE_URI . 'theme/img/app/48.png',
                    'sizes' => '48x48',
                    'type' => 'image/png'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/96.png',
                    'sizes' => '96x96',
                    'type' => 'image/png'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/128.png',
                    'sizes' => '128x128',
                    'type' => 'image/png'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png'
                ],
            ],
            'display'=> 'standalone',
            'orientation' => 'portrait-primary',
            'background-color' => '#1C1D5',
            'theme-color' => '#1C1D5'
        ];

        $this->view->assign('json', json_encode($infos));
    }
}
