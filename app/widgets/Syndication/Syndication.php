<?php

class Syndication extends WidgetBase
{
    function load()
    {

    }

    function display()
    {
        ob_clean();

        if(!isset($_GET['f'])) {
            return;
        }
        
        $from = $_GET['f'];
        if(filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $node = 'urn:xmpp:microblog:0';
        } else {
            return;
        }
        
        $pd = new \modl\PostnDAO();
        
        if(isset($from) && isset($node)) {
            $messages = $pd->getPublic($from, $node);
            $this->view->assign('messages', $messages);
        }
        
        if(isset($messages[0])) {
            header("Content-Type: application/atom+xml; charset=UTF-8");

            $cd = new \modl\ContactDAO();

            $this->view->assign('date', date('c'));
            $this->view->assign('contact', $cd->get($from));

            $this->view->assign('uri',  Route::urlize('blog',array($from)));
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
