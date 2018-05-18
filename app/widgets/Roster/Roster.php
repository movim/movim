<?php

use Moxl\Xec\Action\Roster\GetList;
use Moxl\Xec\Action\Roster\AddItem;
use Moxl\Xec\Action\Roster\RemoveItem;
use Moxl\Xec\Action\Presence\Subscribe;
use Moxl\Xec\Action\Presence\Unsubscribe;
use Moxl\Utils;

class Roster extends \Movim\Widget\Base
{
    function load()
    {
        $this->addcss('roster.css');
        $this->addjs('roster.js');
        $this->registerEvent('roster_getlist_handle', 'onRoster', 'contact');
        $this->registerEvent('roster_additem_handle', 'onAdd', 'contact');
        $this->registerEvent('roster_removeitem_handle', 'onDelete', 'contact');
        $this->registerEvent('roster_updateitem_handle', 'onUpdate', 'contact');
        $this->registerEvent('roster', 'onChange', 'contact');
        $this->registerEvent('presence', 'onPresence', 'contact');
    }

    function onChange($packet)
    {
        $this->rpc(
            'MovimTpl.fill',
            '#roster',
            $this->prepareItems()
        );
    }

    function onDelete($packet)
    {
        Notification::append(null, $this->__('roster.deleted'));
    }

    function onPresence($packet)
    {
        if ($packet->content != null){
            $html = $this->prepareItem(
                $packet->content
            );

            if ($html) {
                $this->rpc('MovimTpl.replace', '#'.cleanupId($packet->content->jid), $html);
            }
        }
    }

    function onAdd($packet)
    {
        Notification::append(null, $this->__('roster.added'));
    }

    function onUpdate($packet = false)
    {
        Notification::append(null, $this->__('roster.updated'));
    }

    function onRoster()
    {
        $this->onUpdate();
    }

    /**
     * @brief Force the roster refresh
     * @returns
     */
    function ajaxGetRoster()
    {
        $this->onRoster();
    }

    /**
     * @brief Force the roster refresh
     * @returns
     */
    function ajaxRefreshRoster()
    {
        $r = new GetList;
        $r->request();
    }

    /**
     * @brief Add a contact to the roster and subscribe
     */
    function ajaxAdd($form)
    {
        $r = new AddItem;
        $r->setTo((string)$form->searchjid->value)
          ->setName((string)$form->alias->value)
          ->setGroup((string)$form->group->value)
          ->request();

        $p = new Subscribe;
        $p->setTo((string)$form->searchjid->value)
          ->request();

        Dialog::ajaxClear();
    }

    function prepareItems()
    {
        $view = $this->tpl();
        $view->assign('contacts', $this->user->session
                                             ->contacts()
                                             ->orderBy('jid')
                                             ->get());

        return $view->draw('_roster_list', true);
    }

    function prepareItem(App\Roster $contact)
    {
        $view = $this->tpl();
        $view->assign('contact', $contact);

        return $view->draw('_roster_item', true);
    }
}
