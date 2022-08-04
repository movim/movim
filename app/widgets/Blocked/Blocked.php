<?php

class Blocked extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addcss('blocked.css');
    }

    public function ajaxUnblockContact(string $jid)
    {
        $this->user->reported()->detach($jid);
        $this->user->refreshBlocked();

        Toast::send($this->__('blocked.account_unblocked'));

        $this->rpc('MovimTpl.remove', '#blocked-'.cleanupId($jid));
    }

    public function display()
    {
        $this->view->assign('blocked', $this->user->reported()->orderBy('reported_user.created_at', 'desc')->get());
    }
}