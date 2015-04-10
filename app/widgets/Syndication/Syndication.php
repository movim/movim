<?php

class Syndication extends WidgetBase
{
    function load()
    {

    }

    function display()
    {
        ob_clean();

        if(!$this->get('f')) {
            return;
        }

        $from = $this->get('f');
        if(filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $node = 'urn:xmpp:microblog:0';
        } else {
            return;
        }
        
        $pd = new \modl\PostnDAO();
        $cd = new \modl\ContactDAO();

        $this->view->assign('contact', $cd->get($from, true));
        $this->view->assign('uri',  Route::urlize('blog',array($from)));
        
        if(isset($from) && isset($node)) {
            $messages = $pd->getPublic($from, $node, 10, 0);
            $this->view->assign('messages', $messages);
        }
        
        if(isset($messages[0])) {
            header("Content-Type: application/atom+xml; charset=UTF-8");

            $this->view->assign('date', date('c'));
        }
    }
    
    function prepareTitle($title)
    {
        if($title == null)
            return '...';
        else
            return $this->prepareContent($title, true);     
    }
    
    function prepareContent($content, $title = false)
    {
        if($title)
            return cleanHTMLTags($content);
        else
            return trim(cleanHTMLTags(prepareString($content)));
    }

    function generateUUID($content)
    {
        return generateUUID(serialize($content));
    }

    function prepareUpdated($date)
    {
        return date('c', strtotime($date));
    }
}
