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
        //$this->addjs('angular.js');
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

            /*
            if($c->groupname == '')
                $c->groupname = $this->__('roster.ungrouped');
            else{
                $c->groupname = htmlspecialchars_decode($c->groupname);
            }
            $c->rostername = htmlspecialchars_decode($c->rostername);

            $ac = $c->toRoster();
            $this->prepareContact($ac, $c, $this->getCaps());
            $c = $ac;

            RPC::call('updateContact', json_encode($c));*/

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
        /*$results = $this->prepareRoster();

        RPC::call('initContacts', $results['contacts']);
        RPC::call('initGroups', $results['groups']);*/
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
            RPC::call('MovimUtils.redirect', Route::urlize('contact', $jid));
            RPC::commit();
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

    /**
     * @brief Get data from database to pass it on to angular in JSON
     * @param
     * @returns $result: a json for the contacts and one for the groups
     */
    /*function prepareRoster()
    {
        //Contacts
        $contactdao = new \Modl\ContactDAO();
        $contacts = $contactdao->getRoster();

        $capsarr = $this->getCaps();

        $result = [];

        $farray = []; //final array
        if(isset($contacts)) {
            // Init
            $c = array_shift($contacts);
            if($c->groupname == ''){
                $c->groupname = $this->__('roster.ungrouped');
            }
            $jid = $c->jid;
            $groupname = $c->groupname;
            $ac = $c->toRoster();
            $this->prepareContact($ac, $c, $capsarr);

            $garray = []; //group array
            $garray['agroup'] = $groupname;
            $garray['tombstone'] = false;
            $garray['agroupitems'] = []; //group array of jids

            $jarray = []; //jid array
            $jarray['ajid'] = $jid;
            $jarray['atruename'] = $ac['rosterview']['name'];
            $jarray['aval'] = $ac['value'];
            $jarray['tombstone'] = false;
            $jarray['ajiditems'] = $ac; //jid array of resources

            array_push($garray['agroupitems'], $jarray);

            foreach($contacts as &$c) {
                // jid has changed
                if($jid != $c->jid){
                    if($c->groupname == ''){
                        $c->groupname = $this->__('roster.ungrouped');
                    }
                    $ac = $c->toRoster();
                    $this->prepareContact($ac, $c, $capsarr);

                    if($groupname != $c->groupname && $c->groupname != ""){
                        //close group
                        array_push($farray, $garray);
                        //next group
                        $groupname = $ac['groupname'];
                        $garray = [];
                        $garray['agroup'] = $groupname;
                        $garray['tombstone'] = false;
                        $garray['agroupitems'] = [];
                    }
                    //push new jid in group
                    $jid = $ac['jid'];
                    $jarray['ajid'] = $jid;
                    $jarray['atruename'] = $ac['rosterview']['name'];
                    $jarray['aval'] = $ac['value'];
                    $jarray['tombstone'] = false;
                    $jarray['ajiditems'] = $ac; //jid array of resources
                    array_push($garray['agroupitems'], $jarray);
                }
                if($c == $contacts[count($contacts)-1]){
                    array_push($farray, $garray);
                }
            }
        }
        $result['contacts'] = json_encode($farray);

        //Groups
        $rd = new \Modl\RosterLinkDAO();
        $groups = $rd->getGroups();
        if(is_array($groups) && !in_array("Ungrouped", $groups)) $groups[] = "Ungrouped";
        else $groups = [];

        $groups = array_flip($groups);
        $result['groups'] = json_encode($groups);

        return $result;
    }*/

    /**
     * @brief Get data for contacts display in roster
     * @param   &$c: the contact as an array and by reference,
     *          $oc: the contact as an object,
     *          $caps: an array of capabilities
     * @returns
     */
    /*
    function prepareContact(&$c, $oc, $caps)
    {
        $presencestxt = getPresencesTxt();

        // We add some basic information
        $c['rosterview']   = [];
        $c['rosterview']['avatar']   = $oc->getPhoto('s');
        $c['rosterview']['color']    = stringToColor($oc->jid);
        $c['rosterview']['name']     = $oc->getTrueName();
        $c['rosterview']['friendpage']     = $this->route('contact', $oc->jid);
        $c['rosterview']['subscription']   = $oc->rostersubscription;

        // Some data relative to the presence
        if($oc->last != null && $oc->last > 60)
            $c['rosterview']['inactive'] = 'inactive';
        else
            $c['rosterview']['inactive'] = '';

        if($oc->value && $oc->value != 5){
            if($oc->value && $oc->value == 6) {
                $c['rosterview']['presencetxt'] = 'server_error';
            } else {
                $c['rosterview']['presencetxt'] = $presencestxt[$oc->value];
            }
            $c['value'] = intval($c['value']);
        } else {
            $c['rosterview']['presencetxt'] = 'offline';
            $c['value'] = 5;
        }

        $c['rosterview']['type']   = '';
        $c['rosterview']['client'] = '';
        $c['rosterview']['jingle'] = false;

        // About the entity capability
        if($caps && isset($caps[$oc->node.'#'.$oc->ver])) {
            $cap  = $caps[$oc->node.'#'.$oc->ver];
            $c['rosterview']['type'] = $cap->type;

            $client = $cap->name;
            $client = explode(' ',$client);
            $c['rosterview']['client'] = strtolower(preg_replace('/[^a-zA-Z0-9_ \-()\/%-&]/s', '', reset($client)));

            // Jingle support
            $features = $cap->features;
            $features = unserialize($features);
            if(array_search('urn:xmpp:jingle:1', $features) !== null
            && array_search('urn:xmpp:jingle:apps:rtp:audio', $features) !== null
            && array_search('urn:xmpp:jingle:apps:rtp:video', $features) !== null
            && (  array_search('urn:xmpp:jingle:transports:ice-udp:0', $features)
               || array_search('urn:xmpp:jingle:transports:ice-udp:1', $features))
            ){
                $c['rosterview']['jingle'] = true;
            }
        }

        // Tune
        $c['rosterview']['tune'] = false;

        if(($oc->tuneartist != null && $oc->tuneartist != '')
            || ($oc->tunetitle  != null && $oc->tunetitle  != ''))
            $c['rosterview']['tune'] = true;
    }*/

    function prepareItems()
    {
        $cd = new \Modl\ContactDAO();
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
