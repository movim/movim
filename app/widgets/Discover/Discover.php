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
                        <a href="'.Route::urlize('blog', array($user->jid, 'urn:xmpp:microblog:0')).'">
                            <img class="avatar" src="'.$user->getPhoto('m').'"/>
                        </a>
                        <div class="postbubble profile">
                            <span class="name">
                                <a href="'.Route::urlize('blog', array($user->jid, 'urn:xmpp:microblog:0')).'">'.$user->getTrueName().'</a>
                            </span>
                            <span class="asv">'.
                                $user->getAge().' '.
                                $gender[$user->gender].' '.
                                $marital[$user->marital].'
                            </span>
                            <div class="content">'.prepareString($user->description).'</div>
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
            <h1><?php echo t('Last registered'); ?></h1>
            <div class="paddedtopbottom">
            </div>
            <div id="contactsresult">
                <?php echo $this->prepareContacts(); ?>
            </div>
        </div>
    <?php

    }
}
