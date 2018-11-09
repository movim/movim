<?php

use Moxl\Xec\Action\Upload\Request;

class Upload extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('upload.js');
        $this->addcss('upload.css');

        $this->registerEvent('upload_request_handle', 'onRequested');
        $this->registerEvent('upload_request_error', 'onError');
        $this->registerEvent('upload_request_errornotallowed', 'onErrorNotAllowed');
        $this->registerEvent('upload_request_errorfiletoolarge', 'onErrorFileTooLarge');
        $this->registerEvent('upload_request_errorresourceconstraint', 'onErrorResourceConstraint');

        if (php_sapi_name() != 'cli') {
            header('Access-Control-Allow-Origin: *');
        }
    }

    function onRequested($package)
    {
        list($get, $put) = array_values($package->content);
        $this->rpc('Upload.request', $get, $put);
    }

    function onError()
    {
        Notification::append(null, $this->__('upload.error_failed'));
    }

    function onErrorFileTooLarge()
    {
        Notification::append(null, $this->__('upload.error_filesize'));
    }

    function onErrorResourceConstraint()
    {
        Notification::append(null, $this->__('upload.error_resource_constraint'));
    }

    function onErrorNotAllowed()
    {
        Notification::append(null, $this->__('upload.error_not_allowed'));
    }

    function ajaxRequest()
    {
        $view = $this->tpl();
        Dialog::fill($view->draw('_upload'));
    }

    function ajaxSend($file)
    {
        $upload = $this->user->session->getUploadService();

        if ($upload) {
            $r = new Request;
            $r->setTo($upload->node)
              ->setName($file->name)
              ->setSize($file->size)
              ->setType($file->type)
              ->request();
        }
    }

    function ajaxFailed()
    {
        Notification::append(null, $this->__('upload.error_failed'));
    }
}
