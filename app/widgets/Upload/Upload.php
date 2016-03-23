<?php

use Moxl\Xec\Action\Upload\Request;

class Upload extends \Movim\Widget\Base {
    function load()
    {
        $this->addjs('upload.js');
        $this->registerEvent('upload_request_handle', 'onRequested');
        header('Access-Control-Allow-Origin: *');
    }

    function onRequested($package)
    {
        list($get, $put) = array_values($package->content);
        RPC::call('Upload.request', $get, $put);
    }

    function ajaxRequest()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_upload', true));
        RPC::call('Upload.init');
    }

    function ajaxSend($file)
    {
        $id = new \Modl\ItemDAO;
        $u = $id->getUpload($this->user->getServer());

        if(isset($u)) {
            $r = new Request;
            $r->setTo($u->node)
              ->setName($file->name)
              ->setSize($file->size)
              ->setType($file->type)
              ->request();
        }
    }

    function display()
    {

    }
}
