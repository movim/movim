<?php

namespace Movim;

use Movim\Controller\Base;

class Route extends Base
{
    public $_routes;
    private $_page;

    public function __construct()
    {
        $this->_routes = [
                'about'         => ['x'],
                'account'       => false,
                'accountnext'   => ['s', 'err'],
                'ajax'          => false,
                'admin'         => false,
                'blog'          => ['f', 'i'],
                'chat'          => ['f', 'r'],
                'community'     => ['s', 'n', 'i'],
                'conf'          => false,
                'contact'       => ['s'],
                'disconnect'    => ['err'],
                'feed'          => ['s', 'n'],
                'help'          => false,
                'home'          => ['i'],
                'infos'         => false,
                'login'         => ['i'],
                'main'          => false,
                'node'          => ['s', 'n', 'i'],
                'news'          => ['s', 'n', 'i'],
                'post'          => ['s', 'n', 'i'],
                'picture'       => ['url'],
                'popuptest'     => false,
                'publish'       => ['s', 'n', 'i', 'sh'],
                'room'          => ['r'],
                'share'         => ['url'],
                'system'        => false,
                'tag'           => ['t', 'i'],
                'visio'         => ['f', 's'],
            ];
    }

    public function find()
    {
        $this->fix($_GET, $_SERVER['QUERY_STRING']);

        $gets = array_keys($_GET);
        $uri = reset($gets);
        unset($_GET[$uri]);
        $request = explode('/', $uri);

        $this->_page = array_shift($request);

        if (isset($this->_routes[$this->_page])) {
            $route = $this->_routes[$this->_page];
        }

        if (count($request)
            && is_array($route)
        ) {
            $i = 0;
            foreach ($route as $key) {
                if (isset($request[$i])) {
                    $_GET[$key] = $request[$i];
                }
                $i++;
            }
        }

        if (empty($this->_page) || $this->_page == 'main') {
            $this->_page = 'news';
        }

        if (!isset($this->_routes[$this->_page])) {
            $this->_page = 'notfound';
        }

        return $this->_page;
    }

    public static function urlize($page, $params = false, $tab = false)
    {
        $r = new Route();
        $routes = $r->_routes;

        if($page === 'root')
            return BASE_URI;

        if (isset($routes[$page])) {
            $uri = '';

            if ($tab != false) {
                $tab = '#'.$tab;
            } else {
                //We construct a classic URL if the rewriting is disabled
                $uri = BASE_URI . '?'. $page;
            }

            if ($params != false && is_array($params)) {
                foreach ($params as $value) {
                    $uri .= '/' . rawurlencode($value);
                }
            } elseif ($params != false) {
                $uri .= '/' . rawurlencode($params);
            }

            return $uri.$tab;
        } else {
            throw new \Exception(__('Route not set for the page %s', $page));
        }
    }

    private function fix(&$target, $source, $discard = true)
    {
        if ($discard)
            $target = [];

        $source = preg_replace_callback(
            '/(^|(?<=&))[^=[&]+/',
            function ($key) {
                return bin2hex(urldecode($key[0]));
            },
            $source
        );

        parse_str($source, $post);
        foreach ($post as $key => $val) {
            $target[ hex2bin($key) ] = $val;
        }
    }
}

