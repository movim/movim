<?php

use Moxl\Xec\Action\Roster\GetList;
use Moxl\Xec\Action\Roster\AddItem;
use Moxl\Xec\Action\Roster\RemoveItem;
use Moxl\Xec\Action\Presence\Subscribe;
use Moxl\Xec\Action\Presence\Unsubscribe;

class Roster extends \Movim\Widget\Base
{
    function load()
    {
        $this->addcss('roster.css');
        $this->addjs('roster.js');
        $this->registerEvent('roster_getlist_handle', 'onRoster');
        $this->registerEvent('roster_additem_handle', 'onAdd');
        $this->registerEvent('roster_removeitem_handle', 'onDelete');
        $this->registerEvent('roster_updateitem_handle', 'onUpdate');
        $this->registerEvent('roster', 'onChange');
        $this->registerEvent('presence', 'onPresence', 'contacts');
    }

    function onChange($packet)
    {
        RPC::call(
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
        $contacts = $packet->content;
        if($contacts != null){
            $cd = new \Modl\ContactDAO();

            $contact = $contacts[0];

            $html = $this->prepareItem($cd->getRoster($contact->jid)[0]);
            if($html) {
                RPC::call('MovimTpl.replace', '#'.cleanupId($contact->jid), $html);
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
     * @brief Display the search contact form
     */
    function ajaxDisplaySearch($jid = null)
    {
        $view = $this->tpl();

        $rd = new \Modl\RosterLinkDAO();

        $view->assign('jid', $jid);
        $view->assign('add',
            $this->call(
                'ajaxAdd',
                "MovimUtils.parseForm('add')"));
        $view->assign('groups', $rd->getGroups());
        $view->assign('search', $this->call('ajaxDisplayFound', 'this.value'));

        Dialog::fill($view->draw('_roster_search', true));
    }

    /**
     * @brief Return the found jid
     */
    function ajaxDisplayFound($jid)
    {
        if($jid != '') {
            $cd = new \Modl\ContactDAO();
            $contacts = $cd->searchJid($jid);

            $view = $this->tpl();
            $view->assign('contacts', $contacts);
            $html = $view->draw('_roster_search_results', true);

            RPC::call('MovimTpl.fill', '#search_results', $html);
        }
    }

    /**
     * @brief Add a contact to the roster and subscribe
     */
    function ajaxAdd($form)
    {
        $r = new AddItem;
        $r->setTo($form->searchjid->value)
          ->setFrom($this->user->getLogin())
          ->setName($form->alias->value)
          ->setGroup($form->group->value)
          ->request();

        $p = new Subscribe;
        $p->setTo($form->searchjid->value)
          ->request();
    }

    /**
     * @brief Remove a contact to the roster and unsubscribe
     */
    function ajaxDelete($jid)
    {
        $r = new RemoveItem;
        $r->setTo($jid)
          ->request();

        $p = new Unsubscribe;
        $p->setTo($jid)
          ->request();
    }

    /**
     *  @brief Search for a contact to add
     */
    function ajaxSearchContact($jid)
    {
        if(filter_var($jid, FILTER_VALIDATE_EMAIL)) {
            RPC::call('MovimUtils.redirect', $this->route('contact', $jid));
        } else
            Notification::append(null, $this->__('roster.jid_error'));
    }

    /*private function getCaps()
    {
        $capsdao = new \Modl\CapsDAO();
        $caps = $capsdao->getAll();

        $capsarr = [];
        foreach($caps as $c) {
            $capsarr[$c->node] = $c;
        }

        return $capsarr;
    }*/

    function prepareItems()
    {
        $cd = new \Modl\ContactDAO;
        $this->user->reload(true);

        $view = $this->tpl();
        $view->assign('contacts', $cd->getRoster());
        $view->assign('offlineshown', $this->user->getConfig('roster'));
        $view->assign('presencestxt', getPresencesTxt());

        return $view->draw('_roster_list', true);
    }

    function prepareItem($contact)
    {
        $view = $this->tpl();
        $view->assign('contact', $contact);
        $view->assign('presencestxt', getPresencesTxt());

        return $view->draw('_roster_item', true);
    }

    function display()
    {
    }
}
