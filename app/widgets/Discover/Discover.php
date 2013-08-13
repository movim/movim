<?php

class Discover extends WidgetCommon {
    function WidgetLoad()
    {

    }

    function prepareContacts() {
        $html = '';
                
        $cd = new \modl\ContactDAO();
        $users = array_reverse($cd->getAllPublic());
        
        $gender = getGender();
        $marital = getMarital();
                
        foreach($users as $user) {
            $html .= '
                <div class="post">
                    <a href="'.Route::urlize('blog', array($user->jid, false)).'">
                        <img class="avatar" src="'.$user->getPhoto('m').'"/>
                        <div class="postbubble">
                            <span class="name">'.
                                $user->getTrueName().'
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
                    </a>
                </div>
                ';
        }

        return $html;
    }

    function build()
    {
    ?>
        <div id="discover">
            <div class="paddedtopbottom">
            <h2><?php echo t('Last registered'); ?></h2>
            </div>
            <div id="contactsresult">
                <?php echo $this->prepareContacts(); ?>
            </div>
        </div>
    <?php

    }
}
