<?php

namespace App\Widgets\CommunityConfig;

use App\Widgets\Dialog\Dialog;
use App\Widgets\Drawer\Drawer;
use App\Widgets\Toast\Toast;
use Movim\Image;
use Movim\Librairies\XMPPtoForm;
use Movim\Widget\Base;

use Moxl\Xec\Action\Pubsub\GetConfig;
use Moxl\Xec\Action\Pubsub\SetConfig;
use Moxl\Xec\Action\Avatar\Set as AvatarSet;
use Moxl\Xec\Payload\Packet;

class CommunityConfig extends Base
{
    public function load()
    {
        $this->registerEvent('pubsub_getconfig_handle', 'onConfig', 'community');
        $this->registerEvent('pubsub_setconfig_handle', 'onConfigSaved', 'community');
        $this->registerEvent('pubsub_setconfig_error', 'onConfigError', 'community');
        $this->registerEvent('avatar_set_pubsub', 'onAvatarSet');
    }

    public function onConfig(Packet $packet)
    {
        list($config, $accessModel, $origin, $node, $advanced) = array_values($packet->content);

        $view = $this->tpl();

        $xml = new XMPPtoForm;
        $form = $xml->getHTML($config->x);

        $view->assign('form', $form);
        $view->assign('server', $origin);
        $view->assign('node', $node);
        $view->assign('config', ($advanced) ? false : $xml->getArray($config->x));
        $view->assign('attributes', $config->attributes());

        Drawer::fill('community_config', $view->draw('_communityconfig'), tiny: true);
        $this->rpc('MovimUtils.applyAutoheight');
    }

    public function onAvatarSet(Packet $packet)
    {
        $this->rpc('Dialog_ajaxClear');
        Toast::send($this->__('avatar.updated'));
    }

    public function onConfigSaved()
    {
        Toast::send($this->__('communityaffiliation.config_saved'));
    }

    public function onConfigError(Packet $packet)
    {
        Toast::send(
            $packet->content ??
            $this->__('communityaffiliation.config_error')
        );
    }

    public function ajaxGetAvatar($origin, $node)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $view = $this->tpl();
        $view->assign('info', \App\Info::where('server', $origin)
                                       ->where('node', $node)
                                       ->first());

        Dialog::fill($view->draw('_communityconfig_avatar'));
    }

    public function ajaxSetAvatar($origin, $node, $form)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $key = $origin.$node.'avatar';

        $p = new Image;
        $p->fromBase($form->photobin->value);
        $p->setKey($key);
        $p->save(false, false, 'jpeg', 60);

        // Reload the freshly compressed picture
        $p->load('jpeg');

        $r = new AvatarSet;
        $r->setTo($origin)
          ->setNode($node)
          ->setUrl(Image::getOrCreate($key, false, false, 'jpeg', true))
          ->setData($p->toBase())
          ->request();
    }

    public function ajaxGetConfig($origin, $node, $advanced = false)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $r = new GetConfig;
        $r->setTo($origin)
          ->setNode($node);

        if ($advanced) {
            $r->enableAdvanced();
        }

        $r->request();
    }

    public function ajaxSetConfig(\stdClass $data, $origin, $node)
    {
        if (!validateServerNode($origin, $node)) {
            return;
        }

        $r = new SetConfig;
        $r->setTo($origin)
          ->setNode($node)
          ->setData(formToArray($data))
          ->request();
    }
}
