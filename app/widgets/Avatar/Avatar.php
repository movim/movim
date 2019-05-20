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
        $this->rpc('MovimTpl.fill', '#avatar_form', $this->prepareForm());
        Notification::toast($this->__('avatar.updated'));
    }

    public function onMyAvatarError()
    {
        $this->rpc('MovimTpl.fill', '#avatar_form', $this->prepareForm());
        Notification::toast($this->__('avatar.not_updated'));
    }

    public function prepareForm()
    {
        $avatarform = $this->tpl();

        $p = new Picture;
        $p->get($this->user->id);

        $avatarform->assign('photobin', $p->toBase());
        $avatarform->assign('me', \App\Contact::firstOrNew(['id' => $this->user->id]));
        $avatarform->assign(
            'submit',
            $this->call('ajaxSubmit', "MovimUtils.formToJson('avatarform')")
            );

        return $avatarform->draw('_avatar_form');
    }

    public function ajaxGetAvatar()
    {
        $r = new Get;
        $r->setTo($this->user->id)
          ->request();
    }

    public function ajaxDisplay()
    {
        $this->rpc('MovimTpl.fill', '#avatar_form', $this->prepareForm());
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
