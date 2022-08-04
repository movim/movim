<?php

use App\Reported;

class AdminReported extends \Movim\Widget\Base
{
    public function load()
    {
        $this->addcss('adminreported.css');
        $this->addjs('adminreported.js');
    }

    public function ajaxBlock(string $jid, bool $checked)
    {
        if (!$this->user->admin) return;

        $reported = Reported::where('id', $jid)->first();

        $reported->blocked = $checked;
        $reported->save();

        if ($checked) {
            Toast::send($this->__('blocked.account_blocked'));
        } else {
            Toast::send($this->__('blocked.account_unblocked'));
        }

        $this->user->refreshBlocked();
    }

    public function ajaxHttpGet()
    {
        if (!$this->user->admin) return;

        $this->rpc('MovimTpl.fill', '#adminreported_widget', $this->prepareReported());
    }

    private function prepareReported()
    {
        $view = $this->tpl();
        $view->assign('reported', Reported::with('users')->orderBy('created_at', 'desc')->get());
        return $view->draw('_adminreported_reported');
    }
}