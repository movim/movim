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
                'admin'         => false,
                'blog'          => ['f', 'i'],
                'tag'           => ['t', 'i'],
                'chat'          => ['f', 'r'],
                'conf'          => false,
                'contact'       => ['f'],
                'disconnect'    => ['err'],
                'feed'          => ['s', 'n'],
                'main'          => false,
                'node'          => ['s', 'n', 'i'],
                'community'     => ['s', 'n', 'i'],
                'help'          => false,
                'infos'         => false,
                'login'         => ['err'],
                'news'          => ['s', 'n', 'i'],
                'post'          => ['s', 'n', 'i'],
                'publish'       => ['s', 'n', 'i', 'sh'],
                'room'          => ['r'],
                'share'         => ['url'],
                'visio'         => false
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

        if(isset($this->_routes[$this->_page]))
            $route = $this->_routes[$this->_page];

        if(count($request) && isset($route)) {
            $i = 0;
            foreach($route as $key) {
                if (isset($request[$i])) {
                    $_GET[$key] = $request[$i];
                }
                $i++;
            }
        }

        if(empty($this->_page) || $this->_page == 'main')
            $this->_page = 'news';

        if(!isset($this->_routes[$this->_page]))
            $this->_page = 'notfound';

        return $this->_page;
    }

    public static function urlize($page, $params = false, $tab = false)
    {
        $r = new Route();
        $routes = $r->_routes;

        if($page === 'root')
            return BASE_URI;

        if(isset($routes[$page])) {
            $uri = '';

            if($tab != false) {
                $tab = '#'.$tab;

            //We construct a classic URL if the rewriting is disabled
            } else {
                $uri = BASE_URI . '?'. $page;
            }

            if($params != false && is_array($params)) {
                foreach($params as $value) {
                    $uri .= '/' . rawurlencode($value);
                }
            } elseif($params != false) {
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
            function($key) { return bin2hex(urldecode($key[0])); },
            $source
        );

        parse_str($source, $post);
        foreach($post as $key => $val)
            $target[ hex2bin($key) ] = $val;
    }
}
