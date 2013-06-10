<?php 

class Route extends ControllerBase {
    public $_routes;
    
    public function __construct() {
        $this->_routes = array(
                'account'       => false,
                'main'          => false,
                'admin'         => false,
                'explore'       => false,
                'profile'       => false,
                'media'         => false,
                'conf'          => false,
                'help'          => false,
                'about'         => false,
                'disconnect'    => array('err'),
                'friend'        => array('f'),
                'blog'          => array('f'),
                'feed'          => array('f'),
                'node'          => array('s', 'n'),
                'server'        => array('s'),
            );
        
        $q = $this->fetch_get('query');
        return $this->find($q);
    }
    
    private function find($q) {        
        // We decompose the URL
        $request = explode('/', $q);
                
        if(empty($q)) {
            $_GET['q'] = 'main';
            return true;
        }
        
        // And we search a pattern for the current page
        elseif(isset($this->_routes[$request[0]])) {
            $route = $this->_routes[$request[0]];
            
            $_GET['q'] = $request[0];
            
            array_shift($request);
            
            // If we find it we see if it's a simple page (with no GET)
            if($route != false) {
                $i = 0;
                foreach($route as $key) {
                    $_GET[$key] = $request[$i];
                    $i++;
                }
            }
            
            return true;
        } else
            return false;
    }
    
    public static function urlize($page, $params = false) {
        $r = new Route();
        $routes = $r->_routes;
        
        if(isset($routes[$page])) {        
            if($params != false && count($routes[$page]) != count($params)) 
                Logger::log(1, t('Route error, please set all the parameters for the page %s', $page));
            else {
                //We construct a classic URL if the rewriting is disabled
                if(!$_SERVER['HTTP_MOD_REWRITE']) {
                    $uri = BASE_URI.'?q='.$page;
                    
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
                // Here we get a beautiful rewriten URL !
                else {
                    $uri = BASE_URI.$page;
                    if($params != false && is_array($params))
                        foreach($params as $value)
                            $uri .= '/'.$value;
                    elseif($params != false)
                        $uri .= '/'.$params;
                }
                return $uri;
            }
        } else
            Logger::log(1, t('Route not set for the page %s', $page));
    }
}
