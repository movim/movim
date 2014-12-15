<?php

class Contact extends WidgetCommon
{
    function load()
    {
    }

    function ajaxGetContact($jid)
    {
        $html = $this->prepareContact($jid);
        RPC::call('movim_fill', 'contact_widget', $html);
    }

    function prepareEmpty($jid)
    {
        $view = $this->tpl();
        $view->assign('jid', $jid);
        return $view->draw('_contact_empty', true);
    }

    function prepareContact($jid)
    {
        $cd = new \Modl\ContactDAO;
        $c  = $cd->get($jid);
        $cd = new \Modl\ContactDAO;
        $cr  = $cd->getRosterItem($jid);

        $view = $this->tpl();

        if(isset($c)) {
            $view->assign('gender' , getGender());
            $view->assign('marital', getMarital());
            $view->assign('mood', getMood());
            
            $view->assign('contact', $c);
            $view->assign('contactr', $cr);
            return $view->draw('_contact', true);
        } elseif(isset($cr)) {
            $view->assign('contact', null);
            $view->assign('contactr', $cr);
            return $view->draw('_contact', true);
        } else {
            return $this->prepareEmpty($jid);
        }
    }

    function display()
    {
    }
}
