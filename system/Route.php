<?php 
class Route extends \BaseController {
    public $_routes;
    private $_page;
    
    public function __construct() {
        $this->_routes = array(
                'account'       => false,
                'accountnext'   => array('s', 'err'),
                'visio'         => false,
                'main'          => false,
                'news'          => false,
                'loading'       => false,
                'admin'         => false,
                'explore'       => false,
                'discover'      => false,
                'profile'       => false,
                'infos'         => false,
                'media'         => array('f'),
                'conf'          => false,
                'help'          => false,
                'about'         => false,
                'login'         => array('err'),
                'pods'          => false,
                'disconnect'    => array('err'),
                'friend'        => array('f'),
                'blog'          => array('f', 'n'),
                'feed'          => array('f', 'n'),
                'nodeconfig'    => array('s', 'n'),
                'node'          => array('s', 'n'),
                'server'        => array('s'),
            );
    }
    
    public function find() {
        if(isset($_SERVER['HTTP_MOD_REWRITE']) && $_SERVER['HTTP_MOD_REWRITE']) {
            $request = explode('/', $this->fetchGet('query'));
            $this->_page = $request[0];
            array_shift($request);

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
        } else {
            $this->_page = $this->fetchGet('q');
        }

        if(empty($this->_page))
            $this->_page = 'main';

        if(!isset($this->_routes[$this->_page]))
            $this->_page = 'notfound';

        return $this->_page;
    }
    
    public static function urlize($page, $params = false, $tab = false) {
        $r = new Route();
        $routes = $r->_routes;

        if($page === 'root')
            return BASE_URI;
        
        if(isset($routes[$page])) {        
            if($params != false && count($routes[$page]) != count($params)) {
                throw new Exception(__('error.route', $page));
            } else {
                if($tab != false)
                    $tab = '#'.$tab;
                //We construct a classic URL if the rewriting is disabled
                if(!isset($_SERVER['HTTP_MOD_REWRITE']) || !$_SERVER['HTTP_MOD_REWRITE']) {
                    $uri = BASE_URI . '?q='.$page;
                    
                    if($params != false && is_array($params)) {
                        $i = 0;
                        foreach($params as $value) {
                            $uri .= '&'.$routes[$page][$i].'='.$value;
                            $i++;
                        }
                    }
                    elseif($params != false)
                        $uri .= '&'.$routes[$page][0].'='.$params;
                } 
                // Here we got a beautiful rewriten URL !
                else {
                    $uri = BASE_URI . $page;
                    if($params != false && is_array($params))
                        foreach($params as $value)
                            $uri .= '/'.$value;
                    elseif($params != false)
                        $uri .= '/'.$params;
                }
                return $uri.$tab;
            }
        } else {
            throw new Exception(__('Route not set for the page %s', $page));
        }
    }
}
