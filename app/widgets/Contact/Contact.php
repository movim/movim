<?php

use Moxl\Xec\Action\Roster\UpdateItem;
use Moxl\Xec\Action\Vcard\Get;
use Respect\Validation\Validator;
use Moxl\Xec\Action\Pubsub\GetItems;

class Contact extends WidgetBase
{
    function load()
    {
        $this->registerEvent('roster_updateitem_handle', 'onContactEdited');
        $this->registerEvent('vcard_get_handle', 'onVcardReceived');
    }

    public function onVcardReceived($packet)
    {
        $contact = $packet->content;
        $this->ajaxGetContact($contact->jid);
    }

    public function onContactEdited($packet)
    {
        Notification::append(null, $this->__('edit.updated'));
    }

    function ajaxClear()
    {
        $html = $this->prepareEmpty();
        RPC::call('movim_fill', 'contact_widget', $html);
    }

    function ajaxGetContact($jid)
    {
        if(!$this->validateJid($jid)) return;

        $html = $this->prepareContact($jid);
        $header = $this->prepareHeader($jid);
        
        Header::fill($header);
        RPC::call('movim_fill', 'contact_widget', $html);
        RPC::call('MovimTpl.showPanel');
    }

    function ajaxEditSubmit($form)
    {
        $rd = new UpdateItem;
        $rd->setTo(echapJid($form['jid']))
           ->setFrom($this->user->getLogin())
           ->setName(htmlspecialchars($form['alias']))
           ->setGroup(htmlspecialchars($form['group']))
           ->request();
    }

    function ajaxRefreshFeed($jid)
    {
        if(!$this->validateJid($jid)) return;

        $r = new GetItems;
        $r->setTo($jid)
          ->setNode('urn:xmpp:microblog:0')
          ->request();
    }

    function ajaxRefreshVcard($jid)
    {
        if(!$this->validateJid($jid)) return;

        $r = new Get;
        $r->setTo(echapJid($jid))->request();
    }

    function ajaxEditContact($jid)
    {
        if(!$this->validateJid($jid)) return;

        $rd = new \Modl\RosterLinkDAO();
        $groups = $rd->getGroups();
        $rl     = $rd->get($jid);

        $view = $this->tpl();

        if(isset($rl)) {
            $view->assign('submit', 
                $this->call(
                    'ajaxEditSubmit', 
                    "movim_parse_form('manage')"));
            $view->assign('contact', $rl);
            $view->assign('groups', $groups);
        }

        Dialog::fill($view->draw('_contact_edit', true));
    }

    function ajaxChat($jid)
    {
        if(!$this->validateJid($jid)) return;

        $c = new Chats;
        $c->ajaxOpen($jid);
        
        RPC::call('movim_redirect', $this->route('chat', $jid));
    }

    function ajaxDeleteContact($jid)
    {
        if(!$this->validateJid($jid)) return;

        $view = $this->tpl();

        $view->assign('jid', $jid);

        Dialog::fill($view->draw('_contact_delete', true));
    }

    function prepareHeader($jid)
    {
        if(!$this->validateJid($jid)) return;

        $cd = new \Modl\ContactDAO;
        $cr  = $cd->getRosterItem($jid);

        $view = $this->tpl();
        
        $view->assign('jid', echapJS($jid));

        if(isset($cr)) {
            $view->assign('contactr', $cr);
            $view->assign('edit', 
                $this->call(
                    'ajaxEditContact', 
                    "'".echapJS($cr->jid)."'"));
            $view->assign('delete', 
                $this->call(
                    'ajaxDeleteContact', 
                    "'".echapJS($cr->jid)."'"));
        } else {
            $view->assign('contactr', null);
            $c  = $cd->get($jid);
            if(isset($c)) {
                $view->assign('contact', $c);
            } else {
                $view->assign('contact', null);
            }
        }

        return $view->draw('_contact_header', true);
    }

    function prepareEmpty($jid = null)
    {
        if($jid == null) {
            $cd = new \modl\ContactDAO();
            $users = $cd->getAllPublic(0, 10);
            if($users != null){
                $view = $this->tpl();
                $view->assign('users', array_reverse($users));
                return $view->draw('_contact_explore', true);
            } else { 
                return '';
            }
        } else {
            $view = $this->tpl();
            $view->assign('jid', $jid);
            return $view->draw('_contact_empty', true);
        }
    }

    function prepareContact($jid)
    {
        if(!$this->validateJid($jid)) return;

        $cd = new \Modl\ContactDAO;
        $c  = $cd->get($jid, true);

        if($c == null || $c->created == null || $c->isEmpty()) {
            $c = new \Modl\Contact;
            $c->jid = $jid;

            $this->ajaxRefreshVcard($jid);
        }
        
        $cr = $cd->getRosterItem($jid);

        $view = $this->tpl();

        $pd = new \Modl\PostnDAO;
        $gallery = $pd->getGallery($jid);
        $blog    = $pd->getPublic($jid, 'urn:xmpp:microblog:0', 1, 0);

        if(isset($c)) {
            $view->assign('mood', getMood());

            $view->assign('contact', $c);
            $view->assign('contactr', $cr);

            if( $cr->node != null 
                && $cr->ver != null 
                && $cr->node 
                && $cr->ver) {                
                $node = $cr->node.'#'.$cr->ver;

                $cad = new \Modl\CapsDAO();
                $caps = $cad->get($node);

                if(
                    isset($caps)
                    && $caps->name != ''
                    && $caps->type != '' ) {
                    $clienttype = getClientTypes();

                    $view->assign('caps', $caps);
                    $view->assign('clienttype', $clienttype);
                }
            } else {
                $view->assign('caps', null);
            }

            $view->assign('gallery', $gallery);
            $view->assign('blog', $blog);

            $view->assign('chat', 
                $this->call(
                    'ajaxChat', 
                    "'".echapJS($c->jid)."'"));

            return $view->draw('_contact', true);
        } elseif(isset($cr)) {
            $view->assign('contact', null);
            $view->assign('contactr', $cr);

            $view->assign('chat', 
                $this->call(
                    'ajaxChat', 
                    "'".echapJS($cr->jid)."'"));
            
            return $view->draw('_contact', true);
        } else {
            return $this->prepareEmpty($jid);
        }
    }

    function getLastFM($contact)
    {
        $uri = str_replace(
            ' ', 
            '%20', 
            'http://ws.audioscrobbler.com/2.0/?method=album.getinfo&api_key=80c1aa3abfa9e3d06f404a2e781e38f9&artist='.
                $contact->tuneartist.
                '&album='.
                $contact->tunesource.
                '&format=json'
            );

        $json = json_decode(requestURL($uri, 2));
        
        $img = $json->album->image[2]->{'#text'};
        $url = $json->album->url;

        return array($img, $url);
    }

    /**
     * @brief Validate the jid
     *
     * @param string $jid
     */
    private function validateJid($jid)
    {
        $validate_jid = Validator::email()->noWhitespace()->length(6, 60);
        if(!$validate_jid->validate($jid)) return false;
        else return true;
    }

    function display()
    {
        $validate_jid = Validator::email()->length(6, 40);

        $this->view->assign('jid', false);
        if($validate_jid->validate($this->get('f'))) {
            $this->view->assign('jid', $this->get('f'));
        }
    }
}
