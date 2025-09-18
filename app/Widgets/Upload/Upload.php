<?php

namespace App\Widgets\Upload;

use App\Widgets\Dialog\Dialog;
use App\Widgets\Toast\Toast;
use Movim\Widget\Base;

use Moxl\Xec\Action\Upload\Request;

class Upload extends Base
{
    public function load()
    {
        if ($this->me->hasUpload()) {
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
        $content = $package->content;

        $upload = \App\Upload::find($content['id']);
        $upload->puturl = $content['put'];
        $upload->geturl = $content['get'];
        $upload->headers = $content['headers'];
        $upload->save();

        $this->rpc('Upload.request', $this->route('upload', $upload->id), $upload->id);
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

    public function ajaxGetPanel()
    {
        $view = $this->tpl();
        $view->assign('service', $this->me->session->getUploadService());
        Dialog::fill($view->draw('_upload'));
        $this->rpc('Upload.attachEvents');
    }

    public function ajaxPrepare($file)
    {
        $uploadService = $this->me->session->getUploadService();

        if($uploadService) {
            $upload = \App\Upload::firstOrCreate([
                'id' => generateUUID(),
                'user_id' => $this->me->id,
                'jidto' => $uploadService->server,
                'name' => $file->name,
                'size' => $file->size,
                'type' => $file->type
            ]);

            $r = new Request;
            $r->setTo($uploadService->server)
              ->setName($file->name)
              ->setSize($file->size)
              ->setType($file->type)
              ->setId($upload->id)
              ->request();
        }
    }

    public function ajaxSend($file)
    {
        $upload = $this->me->session->getUploadService();

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
