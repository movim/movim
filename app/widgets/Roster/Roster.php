<?php

use Moxl\Xec\Action\Roster\GetList;
use Moxl\Xec\Action\Roster\AddItem;
use Moxl\Xec\Action\Roster\RemoveItem;
use Moxl\Xec\Action\Presence\Subscribe;
use Moxl\Xec\Action\Presence\Unsubscribe;
use Moxl\Xec\Action\IqGateway;
use Moxl\Utils;

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
        $this->registerEvent('iqgateway_get_handle', 'onIqGatewayGet');
        $this->registerEvent('iqgateway_set_handle', 'onIqGatewaySet');
        $this->registerEvent('iqgateway_set_error', 'onIqGatewaySetError');
        $this->registerEvent('roster', 'onChange');
        $this->registerEvent('presence', 'onPresence', 'contacts');
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
        $contacts = $packet->content;
        if($contacts != null){
            $cd = new \Modl\ContactDAO();

            $contact = $contacts[0];

            $html = $this->prepareItem($cd->getRoster($contact->jid)[0]);
            if($html) {
                $this->rpc('MovimTpl.replace', '#'.cleanupId($contact->jid), $html);
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

    function onIqGatewayGet($packet)
    {
        $this->rpc(
            'Roster.addGatewayPrompt',
            $packet->from,
            (string)$packet->content->prompt,
            (string)$packet->content->desc
        );
    }

    function onIqGatewaySet($packet)
    {
        $form = $packet->content['extra'];
        unset($form->gatewayprompt);
        unset($form->gateway);
        $form->searchjid->value = $packet->content['query']->jid;
        $this->ajaxAdd($form);
    }

    function onIqGatewaySetError($packet)
    {
       $this->rpc(
            'Roster.errorGatewayPrompt',
            $packet->content['errorid'],
            $packet->content['message']
        );
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

        $rd = new \Modl\RosterLinkDAO;

        $view->assign('jid', $jid);
        $view->assign('groups', $rd->getGroups());
        $view->assign('search', $this->call('ajaxDisplayFound', 'this.value'));

        if($jid === null) {
            $gateways = $this->gateways();
            $view->assign('gateways', $gateways);

            foreach($gateways as $gateway => $caps) {
                $get = new IqGateway\Get;
                $get->setTo($gateway)->request();
            }
        }

        Dialog::fill($view->draw('_roster_search', true));
        $this->rpc('Roster.addGatewayPrompt', '', 'Jabber ID', 'JID');
        $this->rpc('Roster.drawGatewayPrompt');
    }

    protected function gateways()
    {
        $cd = new \Modl\CapsDAO;
        $pd = new \Modl\PresenceDAO;
        $gateways = [];

        foreach($pd->getAll() as $presence) {
            $caps = $cd->get($presence->node . '#' . $presence->ver);
            if($caps && (
                $caps->category === "gateway" || (
                    $caps->category !== "client" &&
                    in_array("jabber:iq:gateway", $caps->features)
                )
            )) {
                $gateways[$presence->jid] = $caps;
            }
        }

        return $gateways;
    }

    /**
     * @brief Return the found jid
     */
    function ajaxDisplayFound($jid)
    {
        if(!empty($jid)) {
            $cd = new \Modl\ContactDAO;
            $contacts = $cd->searchJid($jid);

            $view = $this->tpl();
            $view->assign('contacts', $contacts);
            $html = $view->draw('_roster_search_results', true);

            $this->rpc('MovimTpl.fill', '#search_results', $html);
        }
    }

    /**
     * @brief Add a contact to the roster and subscribe
     */
    function ajaxAdd($form)
    {
        // If there was a prompt, resolve using jabber:iq:gateway
        if(isset($form->gatewayprompt) && isset($form->gateway)) {
            $set = new IqGateway\Set;
            $set->setTo($form->gateway->value)
                ->setPrompt((string)$form->searchjid->value)
                ->setExtra($form)
                ->request();
            return;
        }

        // If a gateway was selected, and it has a domain-only JID
        // Then we can use either new-style or old-style escaping
        if(isset($form->gateway) && strpos($form->gateway->value, '@') === false) {
            if(in_array('jid\20escaping', $this->gateways()[$form->gateway->value]->features)) {
                $form->searchjid->value = Utils::escapeJidLocalpart($form->searchjid->value).'@'.$form->gateway->value;
            } else {
                $form->searchjid->value = str_replace('@', '%', $form->searchjid->value).'@'.$form->gateway->value;
            }
        }

        $r = new AddItem;
        $r->setTo((string)$form->searchjid->value)
          ->setFrom($this->user->getLogin())
          ->setName((string)$form->alias->value)
          ->setGroup((string)$form->group->value)
          ->request();

        $p = new Subscribe;
        $p->setTo((string)$form->searchjid->value)
          ->request();

        Dialog::ajaxClear();
    }

    /**
     *  @brief Search for a contact to add
     */
    function ajaxSearchContact($jid)
    {
        if(filter_var($jid, FILTER_VALIDATE_EMAIL)) {
            $this->rpc('MovimUtils.redirect', $this->route('contact', $jid));
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
        $view->assign('presences', getPresences());
        $view->assign('presencestxt', getPresencesTxt());

        return $view->draw('_roster_item', true);
    }

    function display()
    {
    }
}
