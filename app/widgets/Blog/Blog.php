<?php

class Blog extends WidgetCommon {
    function WidgetLoad()
    {
        
    }
    
    function build()
    {
        $from = $_GET['f'];

        if($_GET['n'])
            $node = $_GET['n'];
        else
            $_GET['n'] = false;
        
        $pd = new \modl\PostnDAO();
        $messages = $pd->getPublic($from, $node);
        
        echo '
                <div class="posthead spacetop">
                        <a 
                            class="button color orange icon feed merged left" 
                            href="'.Route::urlize('feed',array($from, false)).'"
                            target="_blank"
                        >
                            '.t('Feed').' (Atom)
                        </a>
                </div>';

        echo $this->preparePosts($messages, true);
    }
}
