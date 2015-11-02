<?php

use Respect\Validation\Validator;

class Blog extends WidgetBase {
    public $_paging = 10;

    function load()
    {

    }

    function display()
    {
        if($this->_view == 'grouppublic') {
            $from = $this->get('s');
            $node = $this->get('n');

            if(!$this->validateServerNode($from, $node)) return;

            $this->view->assign('mode', 'group');
            $this->view->assign('server', $from);
            $this->view->assign('node', $node);

            $pd = new \Modl\ItemDAO;
            $this->view->assign('item', $pd->getItem($from, $node));
        } else {
            $from = $this->get('f');

            $cd = new \modl\ContactDAO();
            $c  = $cd->get($from, true);
            $this->view->assign('contact', $c);
            if(filter_var($from, FILTER_VALIDATE_EMAIL)) {
                $node = 'urn:xmpp:microblog:0';
            } else {
                return;
            }
            $this->view->assign('mode', 'blog');
        }

        $pd = new \modl\PostnDAO();        

        if($id = $this->get('i')) {
            if(Validator::int()->between(0, 100)->validate($id)) {
                $messages = $pd->getNodeUnfiltered($from, $node, $id * $this->_paging, $this->_paging + 1);
                $page = $id + 1;
            } elseif(Validator::string()->length(5, 100)->validate($id)) {
                $messages = $pd->getPublicItem($from, $node, $id);
            }
        } else {
            $page = 1;
            $messages = $pd->getNodeUnfiltered($from, $node, 0, $this->_paging + 1);
        }

        if(count($messages) == $this->_paging + 1) {
            array_pop($messages);
            $this->view->assign('more', $page);
        }

        $this->view->assign('posts', $messages);
    }

    private function validateServerNode($server, $node)
    {
        $validate_server = Validator::string()->noWhitespace()->length(6, 40);
        $validate_node = Validator::string()->length(3, 100);

        if(!$validate_server->validate($server)
        || !$validate_node->validate($node)
        ) return false;
        else return true;
    }

    function getComments($post)
    {
        $pd = new \Modl\PostnDAO();
        return $pd->getComments($post);
    }
}
