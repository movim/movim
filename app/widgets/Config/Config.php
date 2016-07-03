<?php

use Moxl\Xec\Action\Storage\Set;
use Respect\Validation\Validator;

class Config extends \Movim\Widget\Base
{
    function load()
    {
        $this->addjs('color/jscolor.js');
        $this->addjs('config.js');
        $this->registerEvent('storage_set_handle', 'onConfig');
    }

    function prepareConfigForm()
    {
        $view = $this->tpl();

        /* We load the user configuration */
        $this->user->reload();

        $l = Movim\i18n\Locale::start();

        $view->assign('languages', $l->getList());
        $view->assign('me',        $this->user->getLogin());
        $view->assign('conf',      $this->user->getConfig());

        $view->assign('submit',
            $this->call(
                'ajaxSubmit',
                "MovimUtils.parseForm('general')"
            )
                . "this.className='button color orange inactive oppose';
                    this.onclick=null;"
        );

        return $view->draw('_config_form', true);
    }

    function onConfig($package)
    {
        $data = (array)$package->content;
        $this->user->setConfig($data);

        $this->refreshConfig();

        Notification::append(null, $this->__('config.updated'));
    }

    function ajaxSubmit($data)
    {
        if(!$this->validateForm($data)) {
            $this->refreshConfig();
            Notification::append(null, $this->__('config.not_valid'));
            return;
        }

        $config = $this->user->getConfig();
        if(isset($config))
            $data = array_merge($config, $data);

        $s = new Set;
        $s->setXmlns('movim:prefs')
          ->setData(serialize($data))
          ->request();
    }

    private function refreshConfig()
    {
        $html = $this->prepareConfigForm();

        RPC::call('movim_fill', 'config_widget', $html);
        RPC::call('Config.load');
    }

    private function validateForm($data)
    {
        $l = Movim\i18n\Locale::start();

        if(Validator::in(array_keys($l->getList()))->validate($data['language'])
        && Validator::in(array('show', 'hide'))->validate($data['roster'])
        && ($data['cssurl'] == '' || Validator::url()->validate($data['cssurl'])))
            return true;
        return false;
    }

    function display()
    {
        $this->view->assign('form', $this->prepareConfigForm());
    }
}
