<?php

class Discover extends WidgetCommon {
    function WidgetLoad()
    {

    }

    function prepareContacts() {
        $html = '';
                
        $cd = new \modl\ContactDAO();
        $users = $cd->getAllPublic();
        if(isset($users)) {
            $users = array_reverse($users);
            
            $gender = getGender();
            $marital = getMarital();
                    
            foreach($users as $user) {
                $html .= '
                    <div class="post">
                        <a href="'.Route::urlize('blog', array($user->jid, false)).'">
                            <img class="avatar" src="'.$user->getPhoto('m').'"/>
                        </a>
                        <div class="postbubble">
                            <span class="name">
                                <a href="'.Route::urlize('blog', array($user->jid, false)).'">'.
                                    $user->getTrueName().'
                                </a>
                            </span>
                            <span class="asv">'.
                                $user->getAge().' '.
                                $gender[$user->gender].' '.
                                $marital[$user->marital].'
                            </span>
                            <div 
                                class="content"
                                style="
                                    overflow: hidden;
                                    text-overflow: ellipsis;
                                    white-space: nowrap;
                                    height: 1.5em;
                                "
                            >'.prepareString($user->desc).'</div>
                        </div>
                        
                    </div>
                    ';
            }
        }

        return $html;
    }

    function build()
    {
    ?>
        <div id="discover">
            <div class="paddedtopbottom">
            <h1><?php echo t('Last registered'); ?></h1>
            </div>
            <div id="contactsresult">
                <?php echo $this->prepareContacts(); ?>
            </div>
        </div>
    <?php

    }
}
