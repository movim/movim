<?php

class AdminController extends BaseController {
    function load() {
        $this->session_only = false;
    }

    function dispatch() {
        if(!isset($_SERVER['PHP_AUTH_USER'])) {
            $this->authenticate();
        } else {
            $conf = \system\Conf::getServerConf();

            if($_SERVER['PHP_AUTH_USER'] == (string)$conf['user'] && sha1($_SERVER['PHP_AUTH_PW']) == (string)$conf['pass']){
                $this->page->setTitle(t('%s - Administration Panel', APP_TITLE));

                $this->page->menuAddLink(t('Home'), 'main');
                $this->page->menuAddLink(t('Administration'), 'admin', true);
            } else
                $this->authenticate();
        }
    }

    /**
     * Return a basic auth page for the administration area
     */
    protected function authenticate(){
        header('WWW-Authenticate: Basic realm="Enter credentials admin/password"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Why are you hitting cancel?';
        exit;
    }

}
