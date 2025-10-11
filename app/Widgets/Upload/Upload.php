<?php

namespace App\Widgets\Upload;

use App\Widgets\Dialog\Dialog;
use Movim\Widget\Base;

use Moxl\Xec\Action\Upload\Request;
use Moxl\Xec\Payload\Packet;

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

    public function onRequested(Packet $packet)
    {
        $content = $packet->content;

        $upload = \App\Upload::find($content['id']);
        $upload->puturl = $content['put'];
        $upload->geturl = $content['get'];
        $upload->headers = $content['headers'];
        $upload->save();

        $this->rpc('Upload.request', $this->route('upload', $upload->id), $upload->id);
    }

    public function onError()
    {
        $this->toast($this->__('upload.error_failed'));
    }

    public function onErrorFileTooLarge()
    {
        $this->toast($this->__('upload.error_filesize'));
    }

    public function onErrorResourceConstraint()
    {
        $this->toast($this->__('upload.error_resource_constraint'));
    }

    public function onErrorNotAllowed()
    {
        $this->toast($this->__('upload.error_not_allowed'));
    }

    public function ajaxGetPanel()
    {
        $view = $this->tpl();
        $view->assign('service', $this->me->session->getUploadService());
        Dialog::fill($view->draw('_upload'));
        $this->rpc('Upload.attachEvents');
    }

    /**
     * Internal functions called by UploadFile announce the XMPP file upload
     */

    public function ajaxHttpUploadXMPP(string $file)
    {
        $this->rpc('Upload.setProgress', 'cloud_upload', $this->__('upload.upload_xmpp'));
    }

    public function ajaxHttpProgressXMPP(int $percentage)
    {
        $this->rpc('Upload.setProgress', 'cloud_upload', $percentage . '% - ' . $this->__('upload.upload_xmpp'));
    }

    public function ajaxPrepare($file)
    {
        $uploadService = $this->me->session->getUploadService();

        if ($uploadService) {
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
        $this->toast($this->__('upload.error_failed'));
    }
}
