<?php

use Movim\Widget\Base;

use Moxl\Xec\Action\Upload\Request;

class Upload extends Base
{
    public function load()
    {
        if ($this->user->hasUpload()) {
            $this->addjs('upload.js');
            $this->addcss('upload.css');
        }

        $this->registerEvent('upload_request_handle', 'onRequested');
        $this->registerEvent('upload_request_error', 'onError');
        $this->registerEvent('upload_request_errornotallowed', 'onErrorNotAllowed');
        $this->registerEvent('upload_request_errorfiletoolarge', 'onErrorFileTooLarge');
        $this->registerEvent('upload_request_errorresourceconstraint', 'onErrorResourceConstraint');

        if (php_sapi_name() != 'cli') {
            header('Access-Control-Allow-Origin: *');
        }
    }

    public function onRequested($package)
    {
        list($get, $put, $headers) = array_values($package->content);
        $this->rpc('Upload.request', $get, $put, $headers);
    }

    public function onError()
    {
        Toast::send($this->__('upload.error_failed'));
    }

    public function onErrorFileTooLarge()
    {
        Toast::send($this->__('upload.error_filesize'));
    }

    public function onErrorResourceConstraint()
    {
        Toast::send($this->__('upload.error_resource_constraint'));
    }

    public function onErrorNotAllowed()
    {
        Toast::send($this->__('upload.error_not_allowed'));
    }

    public function ajaxRequest()
    {
        $view = $this->tpl();
        $view->assign('service', $this->user->session->getUploadService());
        Dialog::fill($view->draw('_upload'));
        $this->rpc('Upload.attachEvents');
    }

    public function ajaxSend($file)
    {
        $upload = $this->user->session->getUploadService();

        if ($upload) {
            $r = new Request;
            $r->setTo($upload->server)
              ->setName($file->name)
              ->setSize($file->size)
              ->setType($file->type)
              ->request();
        }
    }

    public function ajaxFailed()
    {
        Toast::send($this->__('upload.error_failed'));
    }
}
