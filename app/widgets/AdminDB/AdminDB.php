<?php

class AdminDB extends \Movim\Widget\Base
{
    function load() {

    }

    public function ajaxUpdateDatabase()
    {
        $md = \modl\Modl::getInstance();
        $md->check(true);
        RPC::call('MovimUtils.reloadThis');
    }

    public function hidePassword($pass)
    {
        return str_repeat('*', strlen($pass));
    }

    function display()
    {
        $md = \modl\Modl::getInstance();
        $infos = $md->check();

        $errors = '';

        $this->view->assign('infos', $infos);
        $this->view->assign('db_update', $this->call('ajaxUpdateDatabase')
            ."this.className='button color loading';");
        try {
            $md->connect();
        } catch(Exception $e) {
            $errors = $e->getMessage();
        }

        if(file_exists(DOCUMENT_ROOT.'/config/db.inc.php')) {
            require DOCUMENT_ROOT.'/config/db.inc.php';
        }

        $supported = $md->getSupportedDatabases();

        $this->view->assign('connected', $md->_connected);
        $this->view->assign('conf', $conf);
        $this->view->assign('dbtype', $supported[$conf['type']]);
        $this->view->assign('errors', $errors);
    }
}
