<?php 
class Route extends \BaseController {
    public $_routes;
    private $_page;
    
    public function __construct() {
        $this->_routes = array(
                'about'         => array('x'),
                'account'       => false,
                'accountnext'   => array('s', 'err'),
                'admin'         => false,
                'blog'          => array('f', 'i'),
                'chat'          => array('f'),
                'conf'          => false,
                'contact'       => array('f'),
                'disconnect'    => array('err'),
                'feed'          => array('f'),
                'group'         => array('s', 'n'),
                'help'          => false,
                'infos'         => false,
                'login'         => array('err'),
                'main'          => false,
                'media'         => array('f'),
                'news'          => array('n'),
                'pods'          => false,
                'profile'       => false,
                'room'          => array('r'),
                'share'         => array('url'),
                'visio'         => false
            );
    }
    
    public function find() {
        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        if($config->rewrite == true
        && isset($_SERVER['HTTP_MOD_REWRITE'])
        && $_SERVER['HTTP_MOD_REWRITE']) {
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

        $cd = new \Modl\ConfigDAO();
        $config = $cd->get();

        if($page === 'root')
            return BASE_URI;
        
        if(isset($routes[$page])) {        
            //if($params != false && count($routes[$page]) != count($params)) {
                //throw new Exception(__('error.route', $page));
            //} else {
                if($tab != false)
                    $tab = '#'.$tab;
                // Here we got a beautiful rewriten URL !
                if($config->rewrite == true
                && isset($_SERVER['HTTP_MOD_REWRITE'])
                && $_SERVER['HTTP_MOD_REWRITE']) {
                    $uri = BASE_URI . $page;
                    if($params != false && is_array($params))
                        foreach($params as $value)
                            $uri .= '/' . $value;
                    elseif($params != false)
                        $uri .= '/' . $params;
                }
                //We construct a classic URL if the rewriting is disabled
                else {
                    $uri = BASE_URI . '?q=' . $page;
                    
                    if($params != false && is_array($params)) {
                        $i = 0;
                        foreach($params as $value) {
                            $uri .= '&' . $routes[$page][$i] . '=' . $value;
                            $i++;
                        }
                    }
                    elseif($params != false)
                        $uri .= '&'.$routes[$page][0].'='.$params;
                }
                return $uri.$tab;
            //}
        } else {
            throw new Exception(__('Route not set for the page %s', $page));
        }
    }
}
