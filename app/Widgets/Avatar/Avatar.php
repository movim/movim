<?php

namespace App\Widgets\Avatar;

use App\Widgets\Dialog\Dialog;
use App\Widgets\Toast\Toast;
use Movim\Image;
use Moxl\Xec\Action\Avatar\Get;
use Moxl\Xec\Action\Avatar\Set;
use Moxl\Xec\Payload\Packet;

class Avatar extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addcss('avatar.css');
        $this->addjs('avatar.js');

        $this->registerEvent('avatar_get_handle', 'onGetAvatar');
        $this->registerEvent('avatar_set_handle', 'onSetAvatar');
        $this->registerEvent('avatar_set_pubsub', 'onSetBanner');
        $this->registerEvent('avatar_set_errorfeaturenotimplemented', 'onMyAvatarError');
        $this->registerEvent('avatar_set_errorbadrequest', 'onMyAvatarError');
        $this->registerEvent('avatar_set_errornotallowed', 'onMyAvatarError');
    }

    public function onSetAvatar(Packet $packet)
    {
        $this->ajaxGetAvatar();
        $this->rpc('MovimTpl.fill', '#avatar', $this->prepareForm());
    }

    public function onSetBanner(Packet $packet)
    {
        global $loop;

        $loop->addTimer(2, function () {
            $this->rpc('MovimTpl.fill', '#avatar', $this->prepareForm());
        });
    }

    public function onGetAvatar(Packet $packet)
    {
        $this->rpc('MovimTpl.fill', '#avatar', $this->prepareForm());
        $this->rpc('Dialog_ajaxClear');
        Toast::send($this->__('avatar.updated'));
    }

    public function onMyAvatarError()
    {
        $this->rpc('MovimTpl.fill', '#avatar', $this->prepareForm());
        Toast::send($this->__('avatar.not_updated'));
    }

    public function prepareForm()
    {
        $avatarform = $this->tpl();
        $avatarform->assign('me', \App\Contact::firstOrNew(['id' => $this->me->id]));
        return $avatarform->draw('_avatar');
    }

    public function ajaxGetForm()
    {
        $view = $this->tpl();
        $view->assign('me', \App\Contact::firstOrNew(['id' => $this->me->id]));
        Dialog::fill($view->draw('_avatar_form'));
    }

    public function ajaxGetBannerForm()
    {
        $view = $this->tpl();
        $view->assign('me', \App\Contact::firstOrNew(['id' => $this->me->id]));
        Dialog::fill($view->draw('_avatar_banner_form'));
    }

    public function ajaxGetAvatar()
    {
        $r = new Get;
        $r->setTo($this->me->id)
          ->request();
    }

    public function ajaxHttpGetCurrent()
    {
        $this->rpc('MovimTpl.fill', '#avatar', $this->prepareForm());
    }

    public function ajaxSubmit($avatar)
    {
        if (empty($avatar->photobin->value)) return;

        $p = new Image;
        $p->fromBase($avatar->photobin->value);
        $p->setKey($this->me->id.'avatar');
        $p->save(false, false, 'jpeg', 90);

        // Reload
        $p->load('jpeg');

        $r = new Set;
        $r->setData($p->toBase())->request();
        $p->remove();

        $p->remove('jpeg');
    }

    public function ajaxBannerSubmit($banner)
    {
        if (empty($banner->photobin->value)) return;

        $key = $this->me->id.'banner';

        $p = new Image;
        $p->fromBase($banner->photobin->value);
        $p->setKey($key);
        $p->save(false, false, 'jpeg', 60);

        // Reload
        $p->load('jpeg');

        $r = new Set;
        $r->setNode('urn:xmpp:movim-banner:0')
          ->setUrl(Image::getOrCreate($key, false, false, 'jpeg', true))
          ->setWidthMetadata(1280)
          ->setHeightMetadata(320)
          ->setData($p->toBase())
          ->request();
    }
}
