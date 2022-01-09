<?php

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
                'community'     => ['s', 'n', 'i', 'q'],
                'conf'          => false,
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
                'popuptest'     => false,
                'publish'       => ['s', 'n', 'i', 'rs', 'rn', 'ri'],
                'room'          => ['r'],
                'share'         => ['url'],
                'subscriptions' => false,
                'system'        => false,
                'tag'           => ['t', 'i'],
                'visio'         => ['f', 's'],
                'visioaudio'    => ['f', 's'],
            ];
    }

    public function find($page = null)
    {
        $this->fix($_GET, $_SERVER['QUERY_STRING']);

        $gets = array_keys($_GET);
        $uri = reset($gets);
        unset($_GET[$uri]);
        $request = explode('/', $uri);

        $this->_page = $page ?? array_shift($request);

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

    public static function urlize(string $page, $params = false, array $get = [], $tab = false)
    {
        $routes = (new Route)->_routes;

        if (isset($routes[$page])) {
            $uri = BASE_URI . '?'. $page;

            // Specific case for picture that is in a subdirectory for caching purposes
            if ($page == 'picture') {
                $uri = BASE_URI . 'picture/?'.rawurlencode($params);
            } elseif ($params != false) {
                if (is_array($params)) {
                    foreach ($params as $value) {
                        $uri .= '/' . rawurlencode($value ?? '');
                    }
                } else {
                    $uri .= '/' . rawurlencode($params ?? '');
                }
            }

            $get = ($get !== []) ? '&'.http_build_query($get) : '';
            $tab = ($tab != false) ? '#'.$tab : '';

            return $uri.$get.$tab;
        } else {
            throw new \Exception(__('Route not set for the page %s', $page));
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
            $target[ hex2bin($key) ] = $val;
        }
    }
}
