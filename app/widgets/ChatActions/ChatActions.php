<?php

use App\Url;

use Moxl\Xec\Action\Message\Retract;

include_once WIDGETS_PATH.'ContactActions/ContactActions.php';

class ChatActions extends \Movim\Widget\Base
{
    /**
     * @brief Get a Drawer view of a contact
     */
    public function ajaxGetContact($jid)
    {
        $c = new ContactActions;
        $c->ajaxGetDrawer($jid);
    }

    /**
     * @brief Display the message dialog
     */
    public function ajaxShowMessageDialog(string $mid)
    {
        $message = $this->user->messages()
                              ->where('mid', $mid)
                              ->first();

        if ($message) {
            $view = $this->tpl();
            $view->assign('message', $message);

            Dialog::fill($view->draw('_chatactions_message'));
        }
    }

    /**
     * @brief Edit a message
     */
    public function ajaxEditMessage($mid)
    {
        $this->rpc('Dialog.clear');
        $this->rpc('Chat.editMessage', $mid);
    }

    /**
     * @brief Retract a message
     *
     * @param string $to
     * @param string $mid
     * @return void
     */
    public function ajaxHttpDaemonRetract($mid)
    {
        $retract = $this->user->messages()
                              ->where('mid', $mid)
                              ->first();

        if ($retract && $retract->originid) {
            $this->rpc('Dialog.clear');

            $r = new Retract;
            $r->setTo($retract->jidto)
              ->setOriginid($retract->originid)
              ->request();

            $retract->retract();
            $retract->save();

            $packet = new \Moxl\Xec\Payload\Packet;
            $packet->content = $retract;

            $c = new Chat;
            $c->onMessage($packet, false, true);
        }
    }

    /**
     * @brief Try to resolve a message URL
     */
    public function ajaxHttpResolveMessage($mid)
    {
        $message = $this->user->messages()
                              ->where('mid', $mid)
                              ->first();

        if ($message && $message->resolved == false) {
            try {
                $url = new Url;
                $url->resolve(trim($message->body));
                $message->urlid = $url->id;

                if ($url->file) {
                    $message->file = (array)$url->file;
                }

                $this->rpc('Chat.refreshMessage', $message->mid);
            } catch (\Exception $e) {}

            $message->resolved = true;
            $message->save();
        }
    }

    /**
     * @brief Resolve a URL
     */
    public function ajaxHttpResolveUrl(string $url)
    {
        try {
            (new Url)->resolve(trim($url));
        } catch (\Exception $e) {}
        $this->rpc('Chat.disableSending');
    }
}