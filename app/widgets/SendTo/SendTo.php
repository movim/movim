<?php

use Movim\Widget\Base;

class SendTo extends Base
{
    public function load()
    {
        $this->addcss('sendto.css');
    }

    public function ajaxSendSearch($link)
    {
        $view = $this->tpl();

        $uri = explodeXMPPURI($link);

        $view->assign('post', null);
        $view->assign('card', null);

        switch ($uri['type']) {
            case 'post':
                $post = \App\Post::where('server', $uri['params'][0])
                    ->where('node',  $uri['params'][1])
                    ->where('nodeid',  $uri['params'][2])
                    ->first();

                if ($post) {
                    $p = new Post;
                    $view->assign('post', $post);
                    $view->assign('card', $p->prepareTicket($post));
                }
                break;
        }

        $view->assign('uri', $link);
        $view->assign('contacts', $this->user->session
                                       ->topContacts()
                                       ->with('presence')
                                       ->take(15)
                                       ->get());

        Drawer::fill($view->draw('_sendto_share'));
    }

    public function ajaxSend(string $to, $file)
    {
        $file->type = 'xmpp';

        Notification::toast($this->__('sendto.shared'));
        $this->rpc('Drawer.clear');

        $c = new Chat;
        $c->ajaxHttpSendMessage($to, $this->__('sendto.shared_with'), false, false, false, $file);
    }

    public function ajaxGetMoreContacts(string $uri)
    {
        $contacts = $this->user->session->topContacts()->with('presence')->get();
        $this->rpc('MovimTpl.fill', '#sendto_contacts', $this->prepareContacts($contacts, $uri));
    }

    public function prepareContacts($contacts, string $uri)
    {
        $view = $this->tpl();
        $view->assign('uri', $uri);
        $view->assign('contacts', $contacts);

        return $view->draw('_sendto_contacts');
    }
}
