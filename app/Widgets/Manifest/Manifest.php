<?php

namespace App\Widgets\Manifest;

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
                    'src' => BASE_URI . 'theme/img/app/1024_square.png',
                    'sizes' => '1024x1024',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/1024.png',
                    'sizes' => '1024x1024',
                    'type' => 'image/png'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/512_square.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/192_square.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/144.png',
                    'sizes' => '144x144',
                    'type' => 'image/png'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/96.png',
                    'sizes' => '96x96',
                    'type' => 'image/png'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/72.png',
                    'sizes' => '72x72',
                    'type' => 'image/png'
                ],
                [
                    'src' => BASE_URI . 'theme/img/app/48.png',
                    'sizes' => '48x48',
                    'type' => 'image/png'
                ]
            ],
            'display_override' => ['window-controls-overlay'],
            'display'       => 'standalone',
            'orientation'   => 'portrait-primary',
            'background_color' => '#10151A',
            'theme_color'   => '#10151A',
            'id'            => '/login',
            'start_url'     => '/login',
            'launch_handler' => [
                'client_mode' => 'navigate-new',
            ],
            'categories'    => ['news', 'photo', 'social', 'entertainment'],
            'dir'           => 'auto',
            'lang'          => 'en',
            'prefer_related_applications' => false,
            'protocol_handlers' => [[
                'protocol' => 'xmpp',
                'name'     => 'Movim',
                'url' => '/share/%s'
            ]],
            'share_target' => [
                'action' => "/share/",
                'method' => 'POST',
                'params' => [
                    'title' => 'title',
                    'text' => 'description',
                    'url' => 'url'

                ]
            ],
            'handle_links' => 'preferred',
            'edge_side_panel' => ['preferred_width' => 375],
        ];

        $this->view->assign('json', json_encode($infos));
    }
}
