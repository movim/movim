<?php

namespace App\Widgets\SendTo;

use App\MessageFile;
use App\Widgets\Chat\Chat;
use App\Widgets\Drawer\Drawer;
use App\Widgets\Post\Post;
use App\Widgets\Toast\Toast;
use Movim\Template\Partial;
use Movim\Widget\Base;
use Movim\XMPPUri;

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

    public function ajaxShareArticle(string $uri, $osShare = false)
    {
        $view = $this->tpl();

        $view->assign('post', null);
        $view->assign('card', null);
        $view->assign('openlink', false);
        $view->assign('osshare', $osShare);

        $this->resolveUriInView($uri, $view);

        $view->assign('subscriptions', $this->me->subscriptions()
            ->notComments()
            ->orderBy('server')->orderBy('node')
            ->get());

        $contact = $this->me->contact;
        $view->assign('me', ($contact == null) ? new \App\Contact : $contact);

        Drawer::fill('send_to_article', $view->draw('_sendto_article'));
    }

    public function ajaxSendContact(string $uri)
    {
        $view = $this->tpl();

        $view->assign('uri', $uri);
        $view->assign('post', null);
        $view->assign('card', null);

        $this->resolveUriInView($uri, $view);

        $conferences = $this->me->session->conferences()
                            ->with('info', 'contact')
                            ->has('presence')
                            ->get();
        $view->assign('conferences', $conferences);
        $view->assign('contacts', $this->me->session
                                       ->topContacts()
                                       ->with('presence')
                                       ->take($conferences->count() > 0 && $conferences->count() <= 10
                                            ? 20 - $conferences->count()
                                            : 25 )
                                       ->get());

        Drawer::fill('send_to_share', $view->draw('_sendto_share'));
        $this->rpc('SendTo.init');
    }

    public function ajaxSend(string $to, bool $muc, string $uri)
    {
        Toast::send($muc
            ? $this->__('sendto.shared_chatroom')
            : $this->__('sendto.shared_contact')
        );
        $this->rpc('Drawer.clear');

        $message = '';
        $xmppUri = new XMPPUri($uri);

        if ($xmppUri->getType() == 'post') {
            $post = $xmppUri->getPost();

            $message = $post && $post->openlink
                ? $post->openlink->href
                : __('sendto.sharing_post');

            if ($xmppUri->getCategory() == 'story') {
                $message = __('sendto.sharing_story');
            }
        }

        $file = new MessageFile;
        $file->type = 'xmpp/uri';
        $file->url = $uri;

        $c = new Chat();
        $c->sendMessage(
            $to,
            $message,
            $muc,
            file: $file
        );
    }

    public function ajaxGetMoreContacts(string $uri)
    {
        $contacts = $this->me->session->topContacts()->with('presence')->get();
        $this->rpc('MovimTpl.fill', '#sendto_share_contacts', $this->prepareContacts($contacts, $uri));
        $this->rpc('SendTo.init');
    }

    public function prepareContacts($contacts, string $uri)
    {
        $view = $this->tpl();
        $view->assign('uri', $uri);
        $view->assign('contacts', $contacts);

        return $view->draw('_sendto_share_contacts');
    }

    private function resolveUriInView(string $uri, Partial &$view)
    {
        $post = (new XMPPUri($uri))->getPost();

        if ($post) {
            $view->assign('post', $post);
            $view->assign('openlink', $post->openlink ? $post->openlink->href : false);
            $view->assign('card', (new Post)->prepareTicket($post));
        }
    }
}
