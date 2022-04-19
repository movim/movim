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
                /*[
                    'src' => BASE_URI . 'theme/img/app/vectorial_square.svg',
                    'sizes' => '512x512',
                    'type' => 'image/svg+xml',
                    'purpose' => 'maskable'
                ],*/
                [
                    'src' => BASE_URI . 'theme/img/app/512_square.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/96.png',
                    'sizes' => '96x96',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ]
            ],
            'shortcuts'     => [
                [
                    'name'  => $this->__('page.chats'),
                    'url'   => $this->route('chat'),
                    'icons' => [
                        [
                            'src' => BASE_URI . 'theme/img/app/shortcuts/chat.png',
                            'sizes' => '96x96',
                            'type' => 'image/png',
                            'purpose' => 'any'
                        ]
                    ]
                ],
                [
                    'name'  => $this->__('page.publish'),
                    'url'   => $this->route('publish'),
                    'icons' => [
                        [
                            'src' => BASE_URI . 'theme/img/app/shortcuts/publish.png',
                            'sizes' => '96x96',
                            'type' => 'image/png',
                            'purpose' => 'any'
                        ]
                    ]
                ],
                [
                    'name'  => $this->__('page.news'),
                    'url'   => $this->route('news'),
                    'icons' => [
                        [
                            'src' => BASE_URI . 'theme/img/app/shortcuts/news.png',
                            'sizes' => '96x96',
                            'type' => 'image/png',
                            'purpose' => 'any'
                        ]
                    ]
                ]
            ],
            'display'       => 'standalone',
            'orientation'   => 'portrait-primary',
            'background_color' => '#10151A',
            'theme_color'   => '#10151A',
            'start_url'     => '/?login',
        ];

        $this->view->assign('json', json_encode($infos));
    }
}
