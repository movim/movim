<?php

class Blog extends WidgetCommon {
    function WidgetLoad()
    {
        
    }
    
    function build()
    {
        $from = $_GET['f'];
        
        $pd = new \modl\PostnDAO();
        $messages = $pd->getPublic($from);
        
        echo '
                <div class="posthead" style="border-top: 0px;">
                        <a 
                            class="button color orange icon feed merged left" 
                            href="'.Route::urlize('feed',$from).'"
                            target="_blank"
                        >
                            '.t('Feed').' (Atom)
                        </a>
                </div>';

        echo $this->preparePosts($messages, true);
    }
}
