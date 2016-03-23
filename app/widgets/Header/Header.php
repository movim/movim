<?php

class Header extends \Movim\Widget\Base
{
    function load()
    {
    }

    static function fill($html)
    {
        RPC::call('movim_fill', 'header', $html);
    }

    function ajaxReset($view)
    {
        $html = $this->prepareHeader($view);
        RPC::call('movim_fill', 'header', $html);
    }

    function prepareHeader($view = null)
    {
        if($view == null) $view = $this->_view;
        $tpl = $this->tpl();
        return $tpl->draw('_header_'.$view, true);
    }

    function display()
    {
        $this->view->assign('header', $this->prepareHeader());
    }
}
