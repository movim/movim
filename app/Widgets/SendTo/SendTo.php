<?php

namespace App\Widgets\SendTo;

use App\Widgets\Chat\Chat;
use App\Widgets\Drawer\Drawer;
use App\Widgets\Post\Post;
use App\Widgets\Toast\Toast;
use Movim\Template\Partial;
use Movim\Widget\Base;

class SendTo extends Base
{
    public function load()
    {
        $this->addcss('sendto.css');
        $this->addjs('sendto.js');
    }

    public function ajaxOsShare(int $postId)
    {
        $post = \App\Post::where('id', $postId)->first();

        if ($post && $post->openlink) {
            $shared = new \stdClass;
            $shared->title = $post->title;
            $shared->url = $post->openlink->href;
            $shared->text = $post->getSummary();

            $this->rpc('SendTo.shareOs', $shared);
        }
    }

    public function ajaxShareArticle($link, $osShare = false)
    {
        $view = $this->tpl();

        $uri = explodeXMPPURI($link);

        $view->assign('post', null);
        $view->assign('card', null);
        $view->assign('openlink', false);
        $view->assign('osshare', $osShare);

        $this->resolveUriInView($uri, $view);

        $view->assign('subscriptions', $this->user->subscriptions()
            ->notComments()
            ->orderBy('server')->orderBy('node')
            ->get());

        $contact = $this->user->contact;
        $view->assign('me', ($contact == null) ? new \App\Contact : $contact);

        Drawer::fill('send_to_article', $view->draw('_sendto_article'));
    }

    public function ajaxSendContact($link)
    {
        $view = $this->tpl();

        $uri = explodeXMPPURI($link);

        $view->assign('post', null);
        $view->assign('card', null);
        $view->assign('openlink', false);

        $this->resolveUriInView($uri, $view);

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

        Drawer::fill('send_to_share', $view->draw('_sendto_share'));
        $this->rpc('SendTo.init');
    }

    public function ajaxSend(string $to, bool $muc = false, string $message)
    {
        Toast::send($muc
            ? $this->__('sendto.shared_chatroom')
            : $this->__('sendto.shared_contact')
        );
        $this->rpc('Drawer.clear');

        $c = new Chat();
        $c->sendMessage(
            $to,
            $message,
            $muc,
            null
        );
    }

    public function ajaxGetMoreContacts(string $uri)
    {
        $contacts = $this->user->session->topContacts()->with('presence')->get();
        $this->rpc('MovimTpl.fill', '#sendto_share_contacts', $this->prepareContacts($contacts, $uri, ''));
        $this->rpc('SendTo.init');
    }

    public function prepareContacts($contacts, string $uri, $openlink)
    {
        $view = $this->tpl();
        $view->assign('uri', $uri);
        $view->assign('contacts', $contacts);
        $view->assign('openlink', $openlink);

        return $view->draw('_sendto_share_contacts');
    }

    private function resolveUriInView(array $uri, Partial &$view)
    {
        switch ($uri['type']) {
            case 'post':
                $post = \App\Post::where('server', $uri['params'][0])
                    ->where('node',  $uri['params'][1])
                    ->where('nodeid',  $uri['params'][2])
                    ->first();

                if ($post) {
                    $p = new Post();
                    $view->assign('post', $post);
                    $view->assign('openlink', $post->openlink ? $post->openlink->href : false);
                    $view->assign('card', $p->prepareTicket($post));
                }
                break;
        }
    }
}
