<?php
/*
 * SPDX-FileCopyrightText: 2010 Jaussoin Timothée
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Movim;

use Movim\Controller\Base;
use App\User;

class Route extends Base
{
    public $_routes;
    private $_page;
    private $_redirect;

    public function __construct()
    {
        $this->_routes = [
            'about'         => ['x'],
            'account'       => false,
            'accountnext'   => ['s', 'err'],
            'ajax'          => false,
            'ajaxd'         => false,
            'admin'         => false,
            'blog'          => ['f', 'i'],
            'chat'          => ['f', 'r'],
            'community'     => ['s', 'n', 'i'],
            'colors'        => false,
            'configuration' => false,
            'contact'       => ['s'],
            'disconnect'    => ['err'],
            'explore'       => ['s'],
            'feed'          => ['s', 'n'],
            'help'          => false,
            'infos'         => false,
            'login'         => ['i'],
            'main'          => false,
            'manifest'      => false,
            'notfound'      => false,
            'node'          => ['s', 'n', 'i'],
            'news'          => false,
            'post'          => ['s', 'n', 'i'],
            'picture'       => ['url'],
            'publish'       => false,
            'room'          => ['r'],
            'share'         => ['url'],
            'subscriptions' => false,
            'system'        => false,
            'tag'           => ['t', 'i'],
            'upload'        => ['f'],
            'visio'         => ['f', 's'],
            'visioaudio'    => ['f', 's'],
        ];
    }

    public function find($page = null)
    {
        $path = explode('/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        if ($page != null) {
            $this->_page = $page;
        } else if (isset($path[1]) && isset($this->_routes[$path[1]])) {
            $this->_page = $path[1];

            if (is_array($this->_routes[$this->_page])) {
                array_shift($path);
                array_shift($path);

                $i = 0;
                foreach ($this->_routes[$this->_page] as $key) {
                    if (isset($path[$i])) {
                        $_GET[$key] = $path[$i];
                    }

                    $i++;
                }
            }
        } else {
            // Old url format, lets redirect
            $this->fix($_GET, $_SERVER['QUERY_STRING']);

            $gets = array_keys($_GET);
            $uri = reset($gets);
            unset($_GET[$uri]);
            $request = explode('/', $uri);

            $page = array_shift($request);

            foreach ($request as $key => $value) {
                $request[$key] = rawurldecode($value);
            }

            if ($page > 0 && isset($this->_routes[$page])) {
                header('Location: '. Route::urlize($page, $request));
                exit;
            }
        }

        if (empty($this->_page) || $this->_page == 'main') {
            $this->_page = null;

            $user = User::me();

            $this->_redirect = (isLogged() && $user->chatmain)
                ? 'chat'
                : 'news';
        } else if (!isset($this->_routes[$this->_page])) {
            $this->_page = null;
            $this->_redirect = 'notfound';
        }

        return $this->_page;
    }

    public function getRedirect()
    {
        return $this->_redirect;
    }

    public static function urlize(string $page, $params = null, array $get = [], $tab = false): ?string
    {
        $routes = (new Route)->_routes;

        if (isset($routes[$page])) {
            $uri = BASE_URI . $page;

            if ($params != null) {
                if (is_array($params)) {
                    foreach ($params as $value) {
                        $uri .= '/' . rawurlencode($value ?? '');
                    }
                } else {
                    $uri .= '/' . rawurlencode($params ?? '');
                }
            }

            $get = ($get !== []) ? '?' . http_build_query($get) : '';
            $tab = ($tab != false) ? '#' . $tab : '';

            return $uri . $get . $tab;
        } else {
            logError('Route not set for the page ' . $page);

            return null;
        }
    }

    private function fix(&$target, $source, $discard = true)
    {
        if ($discard) {
            $target = [];
        }

        $source = preg_replace_callback(
            '/(^|(?<=&))[^=[&]+/',
            function ($key) {
                return bin2hex($key[0]);
            },
            $source
        );

        parse_str($source, $post);
        foreach ($post as $key => $val) {
            $target[hex2bin($key)] = $val;
        }
    }
}
