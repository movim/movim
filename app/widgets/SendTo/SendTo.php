<?php

use App\MessageFile;
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
        $view->assign('openlink', false);

        switch ($uri['type']) {
            case 'post':
                $post = \App\Post::where('server', $uri['params'][0])
                    ->where('node',  $uri['params'][1])
                    ->where('nodeid',  $uri['params'][2])
                    ->first();

                if ($post) {
                    $p = new Post;
                    $view->assign('post', $post);
                    $view->assign('openlink', $post->openlink ? $post->openlink->href : false);
                    $view->assign('card', $p->prepareTicket($post));
                }
                break;
        }

        $view->assign('uri', $link);
        $conferences = $this->user->session->conferences()
                            ->with('info', 'contact')
                            ->has('presence')
                            ->get();
        $view->assign('conferences', $conferences);
        $view->assign('contacts', $this->user->session
                                       ->topContacts()
                                       ->with('presence')
                                       ->take($conferences->count() > 0 && $conferences->count() <= 10
                                            ? 20 - $conferences->count()
                                            : 25 )
                                       ->get());

        Drawer::fill($view->draw('_sendto_share'));
    }

    public function ajaxSend(string $to, $file, $muc = false, $message = false)
    {
        $file->type = 'xmpp/uri'; // Internal placeholder
        $file->name = $this->__('sendto.shared_with');

        $messageFile = new MessageFile;
        $messageFile->import($file);

        Toast::send($muc
            ? $this->__('sendto.shared_chatroom')
            : $this->__('sendto.shared_contact')
        );
        $this->rpc('Drawer.clear');

        $c = new Chat;
        $c->sendMessage(
            $to,
            !empty($message) ? $message : $this->__('sendto.shared_with'),
            $muc,
            null,
            $messageFile
        );
    }

    public function ajaxGetMoreContacts(string $uri)
    {
        $contacts = $this->user->session->topContacts()->with('presence')->get();
        $this->rpc('MovimTpl.fill', '#sendto_contacts', $this->prepareContacts($contacts, $uri, ''));
    }

    public function prepareContacts($contacts, string $uri, $openlink)
    {
        $view = $this->tpl();
        $view->assign('uri', $uri);
        $view->assign('contacts', $contacts);
        $view->assign('openlink', $openlink);

        return $view->draw('_sendto_contacts');
    }
}
