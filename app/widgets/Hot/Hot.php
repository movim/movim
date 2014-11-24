<?php
class Hot extends WidgetCommon
{
    function load()
    {
    }

    function display()
    {
        $nd = new \modl\ItemDAO();

        $this->view->assign('nodes', $nd->getUpdatedItems(0, 10));
    }

    function getAvatar($server, $node) {
        $user = new \modl\Contact;
        $user->jid = $server.$node;
        return $user->getPhoto('m');
    }
}
