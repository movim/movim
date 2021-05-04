<?php

use Moxl\Xec\Action\Avatar\Get;
use Moxl\Xec\Action\Avatar\Set;

use Movim\Picture;

class Avatar extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addcss('avatar.css');
        $this->addjs('avatar.js');

        $this->registerEvent('avatar_get_handle', 'onGetAvatar');
        $this->registerEvent('avatar_set_handle', 'onSetAvatar');
        $this->registerEvent('avatar_set_errorfeaturenotimplemented', 'onMyAvatarError');
        $this->registerEvent('avatar_set_errorbadrequest', 'onMyAvatarError');
        $this->registerEvent('avatar_set_errornotallowed', 'onMyAvatarError');
    }

    public function onSetAvatar($packet)
    {
        $this->ajaxGetAvatar();
    }

    public function onGetAvatar($packet)
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
        $avatarform->assign('me', \App\Contact::firstOrNew(['id' => $this->user->id]));
        return $avatarform->draw('_avatar');
    }

    public function ajaxGetForm()
    {
        $view = $this->tpl();
        $view->assign('me', \App\Contact::firstOrNew(['id' => $this->user->id]));
        Dialog::fill($view->draw('_avatar_form'));
    }

    public function ajaxGetAvatar()
    {
        $r = new Get;
        $r->setTo($this->user->id)
          ->request();
    }

    public function ajaxHttpGetCurrent()
    {
        $this->rpc('MovimTpl.fill', '#avatar', $this->prepareForm());
    }

    public function ajaxSubmit($avatar)
    {
        $p = new Picture;
        $p->fromBase($avatar->photobin->value);

        $p->set('temp', 'jpeg', 60);

        $p = new Picture;
        $p->get('temp');

        $r = new Set;
        $r->setData($p->toBase())->request();
    }
}
