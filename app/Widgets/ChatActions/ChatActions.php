<?php

namespace App\Widgets\ChatActions;

use App\Message;
use App\Url;
use App\Widgets\Chat\Chat;
use App\Widgets\ContactActions\ContactActions;
use App\Widgets\Dialog\Dialog;
use App\Widgets\Drawer\Drawer;
use App\Widgets\Toast\Toast;
use Moxl\Xec\Action\Blocking\Block;
use Moxl\Xec\Action\Blocking\Unblock;
use Moxl\Xec\Action\Message\Moderate;
use Moxl\Xec\Action\Message\Retract;

use Illuminate\Database\Capsule\Manager as DB;

class ChatActions extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addjs('chatactions.js');
        $this->addcss('chatactions.css');

        $this->registerEvent('blocking_block_handle', 'onBlock');
        $this->registerEvent('blocking_unblock_handle', 'onUnblock');
    }

    public function onBlock($packet)
    {
        Toast::send($this->__('blocked.account_blocked'));
        $this->rpc('Chat_ajaxGet', $packet->content);
    }

    public function onUnblock($packet)
    {
        Toast::send($this->__('blocked.account_unblocked'));
        $this->rpc('Chat_ajaxGet', $packet->content);
    }

    /**
     * @brief Get a Drawer view of a contact
     */
    public function ajaxGetContact(string $jid)
    {
        $c = new ContactActions();
        $c->ajaxGetDrawer($jid);
    }

    /**
     * @brief Block the contact
     */
    public function ajaxBlock(string $jid)
    {
        $block = new Block;
        $block->setJid($jid);
        $block->request();
    }

    /**
     * @brief Unblock the contact
     */
    public function ajaxUnblock(string $jid)
    {
        $unblock = new Unblock;
        $unblock->setJid($jid);
        $unblock->request();
    }

    /**
     * @brief Display the message dialog
     */
    public function ajaxShowMessageDialog(string $mid)
    {
        $message = $this->me->messages()
            ->where('mid', $mid)
            ->with('reactions.contact')
            ->first();

        if ($message && $message->isClassic() && !$message->retracted) {
            $view = $this->tpl();

            $message->body = $message->inlinedBody;

            $view->assign('message', $message);

            if ($message->isMuc()) {
                $view->assign('conference', $this->me->session->conferences()
                    ->where('conference', $message->jidfrom)
                    ->with('info')
                    ->first());
            } else {
                $view->assign('conference', null);
            }

            $this->rpc('ChatActions.setMessage', $message);
            Dialog::fill($view->draw('_chatactions_message_dialog'));
        }
    }

    /**
     * @brief Display the search dialog
     */
    public function ajaxShowSearchDialog(string $jid, ?bool $muc = false)
    {
        if (DB::getDriverName() != 'pgsql') return;

        $view = $this->tpl();
        $view->assign('jid', $jid);
        $view->assign('muc', $muc);

        Drawer::fill('chat_search', $view->draw('_chatactions_search'));

        $this->rpc('ChatActions.focusSearch');
    }

    public function ajaxSearchMessages(string $jid, string $keywords, ?bool $muc = false)
    {
        if (DB::getDriverName() != 'pgsql') return;
        if (!validateJid($jid)) return;

        if (!empty($keywords)) {
            $keywords = str_replace(' ', ' & ', trim($keywords));

            $messagesQuery = \App\Message::jid($jid)
                ->selectRaw('*, ts_headline(\'simple\', body, plainto_tsquery(\'simple\', ?), \'StartSel=<mark>,StopSel=</mark>\') AS headline', [$keywords])
                ->whereRaw('to_tsvector(\'simple\', body) @@ to_tsquery(\'simple\', ?)', [$keywords])
                ->orderBy('published', 'desc')
                ->where('encrypted', false)
                ->where('retracted', false)
                ->take(20);

            $messagesQuery = $muc
                ? $messagesQuery->whereIn('type', Message::MESSAGE_TYPE_MUC)->whereNull('subject')
                : $messagesQuery->whereIn('type', Message::MESSAGE_TYPE);

            $messages = $messagesQuery->get();

            $view = $this->tpl();
            $view->assign('messages', $messages);
            $this->rpc('MovimTpl.fill', '#chat_search', $view->draw('_chatactions_search_result'));
        } else {
            $this->rpc('MovimTpl.fill', '#chat_search', $this->prepareSearchPlaceholder());
        }
    }

    public function prepareMessage(Message $message, ?bool $search = false)
    {
        $view = $this->tpl();
        $view->assign('message', $message);
        $view->assign('search', $search);
        return $view->draw('_chatactions_message');
    }

    public function prepareSearchPlaceholder()
    {
        $view = $this->tpl();
        return $view->draw('_chatactions_search_placeholder');
    }

    public function ajaxCopiedMessageText()
    {
        Toast::send($this->__('chatactions.copied_text'));
    }

    /**
     * @brief Edit a message
     */
    /*public function ajaxEditMessage($mid)
    {
        $this->rpc('Dialog.clear');
        $this->rpc('Chat.editMessage', $mid);
    }*/

    /**
     * @brief Retract a message
     *
     * @param string $mid
     * @return void
     */
    public function ajaxHttpDaemonRetract($mid)
    {
        $retract = $this->me->messages()
            ->where('mid', $mid)
            ->first();

        if ($retract && ($retract->stanzaid || $retract->originid)) {
            $this->rpc('Dialog.clear');

            $r = new Retract;
            $r->setTo($retract->isMuc() ? $retract->jidfrom : $retract->jidto)
                ->setType($retract->type)
                ->setId($retract->stanzaid ?? $retract->originid)
                ->request();

            if (!$retract->isMuc()) {
                $retract->retract();
                $retract->save();
            }

            $packet = new \Moxl\Xec\Payload\Packet;
            $packet->content = $retract;

            $c = new Chat;
            $c->onMessage($packet, false, true);
        }
    }

    /**
     * @brief Moderate a message
     *
     * @param string $mid
     * @return void
     */
    public function ajaxHttpDaemonModerate($mid)
    {
        $retract = $this->me->messages()
            ->where('mid', $mid)
            ->first();

        if ($retract && $retract->stanzaid) {
            $this->rpc('Dialog.clear');

            $r = new Moderate;
            $r->setTo($retract->jidfrom)
                ->setStanzaid($retract->stanzaid)
                ->request();
        }
    }

    /**
     * @brief Try to resolve a message URL
     */
    public function ajaxHttpResolveMessage($mid)
    {
        $message = $this->me->messages()
            ->where('mid', $mid)
            ->first();

        if ($message && $message->resolved == false) {
            try {
                $url = new Url;
                $url->resolve(htmlspecialchars_decode(trim($message->body)));
                $message->urlid = $url->id;

                if ($url->file) {
                    $messageFile = $url->file;
                    $messageFile->message_mid = $message->mid;
                    $messageFile->save();
                }

                $this->rpc('Chat.refreshMessage', $message->mid);
            } catch (\Exception $e) {
                logError($e);
            }

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
            $embed = (new Url)->resolve(trim($url));

            if ($embed != null) {
                $this->rpc('MovimTpl.fill', '#embed', (new Chat)->prepareEmbed($embed));
            }
        } catch (\Exception $e) {
        }
        $this->rpc('Chat.disableSending');
    }
}
