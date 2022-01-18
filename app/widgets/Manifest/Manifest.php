<?php

use Movim\Widget\Base;

class Manifest extends Base
{
    public function display()
    {
        $infos = [
            'name'          => APP_TITLE,
            'short_name'    => APP_TITLE,
            'description'   => __('global.description'),
            'scope'         => BASE_URI,
            'icons'         => [
                [
                    'src' => BASE_URI . 'theme/img/app/vectorial_square.svg',
                    'sizes' => '512x512',
                    'type' => 'image/svg+xml',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/vectorial.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ]
            ],
            'shortcuts'     => [
                [
                    'name'  => $this->__('page.chats'),
                    'url'   => $this->route('chat')
                ],
                [
                    'name'  => $this->__('page.publish'),
                    'url'   => $this->route('publish')
                ],
                [
                    'name'  => $this->__('page.news'),
                    'url'   => $this->route('news')
                ]
            ],
            'display'       => 'standalone',
            'orientation'   => 'portrait-primary',
            'background_color' => '#3F51B5',
            'theme_color'   => '#10151A'
        ];

        $this->view->assign('json', json_encode($infos));
    }
}
