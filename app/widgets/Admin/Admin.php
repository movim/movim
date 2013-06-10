<?php

/**
 * @package Widgets
 *
 * @file Admin.php
 * This file is part of MOVIM.
 *
 * @brief The administration widget.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 25 November 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */
 
class Admin extends WidgetBase {
    private $_conf;
    
    function WidgetLoad()
	{
        $this->_conf = Conf::getServerConf();
    }
    
    private function isValid($what)
    {
        if($what)
            return "message success";
        else
            return "message error";
    }
    
    private function testDir($dir){
        return (file_exists($dir) && is_dir($dir) && is_writable($dir));
    }
    
    private function testFile($file) {
        return (file_exists($file) && is_writable($file));
    }

    /*
     * Create the dirs
     */
    function createDirs(){
        if(!file_exists(BASE_PATH.'cache') && !@mkdir(BASE_PATH.'cache')) {
            echo t("Couldn't create directory '%s'.", 'cache');
            return false;
        }
        
        if(!file_exists(BASE_PATH.'log') && !@mkdir(BASE_PATH.'log')) {
            echo t("Couldn't create directory '%s'.", 'log');
            return false;
        }
        
        if(!file_exists(BASE_PATH.'config') && !@mkdir(BASE_PATH.'config')) {
            echo t("Couldn't create directory '%s'.", 'config');
            return false;
        }
    }
    
    private function listThemes()
    {
        $dir = opendir(BASE_PATH.'themes');
        $themes = array();

        while($theme = readdir($dir)) {
            if(preg_match('/^\.+$/', $theme)
            || !is_dir(BASE_PATH.'themes/'.$theme)) {
                continue;
            }

            $themes[$theme] = $theme;
        }

        return $themes;
    }
    
    private function listLangs()
    {
        $dir = opendir(BASE_PATH.'i18n');
        $langs = array('en' => 'English');
        $languages = get_lang_list();

        while($lang = readdir($dir)) {
            if(!preg_match('/\.po$/', $lang)) {
                continue;
            }

            $lang = substr($lang, 0, strlen($lang) - 3);
            $langs[$lang] = $languages[$lang];
        }

        return $langs;
    }
    
    function testBosh($url) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Fire !
        $rs = array();

        $rs['content'] = curl_exec($ch);
        $rs['errno'] = curl_errno($ch);
        $rs['errmsg'] = curl_error($ch);
        $rs['header'] = curl_getinfo($ch);
        
        if($rs['content'] != false && $rs['content'] != '') {
            return true;
        }

        elseif($rs['errno'] != 0 || $rs['content'] == '') {
            return false;
        }
        curl_close($ch);
    }
    
    public function ajaxAdminSubmit($form)
    {
        unset($form['']);
        
        $empty = false;
        
        if($form['pass'] == '' || !isset($form['pass'])) {
            $form['pass'] = $this->_conf['pass'];
            $form['repass'] = $this->_conf['pass'];
            
            $empty = true;
        }
        
        if($form['pass'] == $form['repass']) {
            unset($form['repass']);
            
            if(!$empty)
                $form['pass'] = sha1($form['pass']);
            
            foreach($this->_conf as $key => $value) {
                if(isset($form[$key]))
                    $this->_conf[$key] = $form[$key];
            }

            Conf::saveConfFile($this->_conf);
        }
    }
    
    public function ajaxRecreateDatabase()
    {
        $pd = new \modl\PostnDAO();
        $pd->create();

        $nd = new \modl\NodeDAO();
        $nd->create();

        $cd = new \modl\ContactDAO();
        $cd->create();

        $cad = new \modl\CapsDAO();
        $cad->create();

        $prd = new \modl\PresenceDAO();
        $prd->create();

        $rd = new \modl\RosterLinkDAO();
        $rd->create();

        $sd = new \modl\SessionDAO();
        $sd->create();

        $cd = new \modl\CacheDAO();
        $cd->create();

        $md = new \modl\MessageDAO();
        $md->create();

        $cd = new \modl\SubscriptionDAO();
        $cd->create();

        $pr = new \modl\PrivacyDAO();
        $pr->create();
    }
    
    private function prepareAdmin()
    {
        $submit = $this->genCallAjax('ajaxAdminSubmit', "movim_parse_form('admin')")
            ."this.className='button icon loading'; setTimeout(function() {location.reload(false)}, 2000);";
            
        if($this->testDir(BASE_PATH))
			$this->createDirs();
        
        $html = '
        <form name="admin" id="adminform">';
        
        /*$html .= '
            <fieldset>
                <legend>'.t('General Informations').'</legend>';
                
            $file = BASE_PATH.'VERSION';
            if($f = fopen($file, 'r')){
                $html .= '
                    <div class="element simple">
                        <label for="fn">'.t('Version').'</label>
                        <span>'.trim(fgets($f)).'</span>
                    </div>';
            }
            
        $html .= '
            </fieldset>';*/
        
        $html .= '
            <fieldset>
                <legend>'.t("Compatibility Check").'</legend>
                    <div class="clear"></div>';

                $html .= 
                    '<p>'.
                        t('Movim requires certain external components. Please install them before you can succeed:').
                    '</p><br />';
                    
                $html .= '                
                    <div class="'.$this->isValid((version_compare(PHP_VERSION, '5.3.0') >= 0)).'">
                        '.t('Your PHP-Version: %s <br>Required: 5.3.0', PHP_VERSION).'
                    </div>
                    <div class="'.$this->isValid(extension_loaded('curl')).'">
                        '.t('CURL-Library').'
                    </div>
                    <div class="'.$this->isValid(extension_loaded('gd')).'">
                        '.t('GD').'
                    </div>
                    <div class="'.$this->isValid(extension_loaded('SimpleXml')).'">
                        '.t('SimpleXML').'
                    </div>
                    <div class="'.$this->isValid($this->testDir(BASE_PATH)).'">
                        '.t('Read and write rights for the webserver in Movim\'s root directory').'
                    </div>
                    <div class="'.$this->isValid($_SERVER['HTTP_MOD_REWRITE']).'">
                        '.t('URL Rewriting support').'
                    </div>';
            
        $html .= '
            </fieldset>';
            
        $html .= '
            <fieldset>
                    <legend>'.t('General Settings').'</legend>
                    <div class="element">
                        <label for="movim" >'.t('Theme').'</label>
                            <div class="select">
                                <select id="theme" name="theme">';

                                    foreach($this->listThemes() as $key => $value) {
                                        if((string)$this->_conf['theme'] == $key)
                                            $sel = 'selected="selected"';
                                        else
                                            $sel = '';
                                        
                                        $html .= '
                                            <option value="'.$key.'" '.$sel.'>'.$value.'</option>';
                                    }

        $html .= '              </select>
                            </div>
                    </div>';
        
        $html .= '
                    <div class="element">
                        <label for="da">'.t('Default language').'</label>
                            <div class="select">
                                <select id="defLang" name="defLang">';
                                    
                                    foreach($this->listLangs() as $key => $value) {
                                        if((string)$this->_conf['defLang'] == $key)
                                            $sel = 'selected="selected"';
                                        else
                                            $sel = '';
                                                                            
                                        $html .= '
                                            <option value="'.$key.'" '.$sel.'>'.$value.'</option>';
                                    }
                    
        $html .= '              </select>
                            </div>
                        </div>';
                        
        $html .= '
                    <div class="element">
                            <label for="maxUsers">'.t('Maximum population').'</label>
                            <input type="text" name="maxUsers" id="maxUsers" value="'.$this->_conf['maxUsers'].'" />
                    </div>';
                    
        $html .= '
                    <div class="element">
                            <label for="sizeLimit">'.t('User folder size limit (in bytes)').'</label>
                            <input type="text" name="sizeLimit" id="sizeLimit" value="'.$this->_conf['sizeLimit'].'" />
                    </div>';
                        
        $logopts = array(
            0 => t('empty'),
            2 => t('terse'),
            4 => t('normal'),
            6 => t('talkative'),
            7 => t('ultimate'),
        );
        
        $default_log = 4;
        
        $html .= '
                    <div class="element">
                        <label for="7">'.t("Log verbosity").'</label>
                        <div class="select">
                            <select id="logLevel" name="logLevel">';
                                foreach($logopts as $lognum => $text) {
                                    if($this->_conf['logLevel'] == $lognum)
                                        $sel = 'selected="selected"';

                                $html .= '
                                    <option value="'.$lognum.'" '.$sel.'>'.
                                        $text.'
                                    </option>';
                                }
        $html .= '          </select>
                        </div>
                    </div>';
                    
        $html .= '  
                    <div class="clear"></div>
                    <a class="button icon yes color green" style="float: right;" onclick="'.$submit.'">'.t('Submit').'</a>';
        
        $html .= '
                </fieldset>';
                
        $html .= '
            <fieldset>
                <legend>'.t("Database Settings").'</legend>
                    <div class="clear"></div>';

                $md = new \modl\ModlDAO();
                if(isset($md->_dao->_error)) {
                    $html .= '
                        <div class="message error">'.
                            t("Modl wasn't able to connect to the database").'<br />
                            '.$md->_dao->_error.'
                        </div>
                    ';
                } else {
                    $dbrecreate = $this->genCallAjax('ajaxRecreateDatabase');
                    
                    $html .= '
                    <div class="element">
                        <label for="db">'.t('Recreate the database').'</label>
                        <a class="button icon loading color red" onclick="'.$dbrecreate.'">'.t('Recreate').'</a>
                    </div>
                    
                    <div class="message warning">
                        '.t('This button will clear and recreate the Movim database.').'
                    </div>
                    ';
                }
                
        $html .= '
                    <div class="element large">
                            <label for="db">'.t('Dabase String').'</label>
                            <input type="text" name="db" id="db" value="'.$this->_conf['db'].'" />
                    </div>';

        $html .= '  
                    <div class="clear"></div>
                    <a class="button icon yes color green" style="float: right;" onclick="'.$submit.'">'.t('Submit').'</a>';
            
        $html .= '
            </fieldset>';
            
        $html .= '
            <fieldset>
                <legend>'.t("Bosh Configuration").'</legend>
                    <div class="clear"></div>';
                    
        $html .= '<p>'.
                    t("Enter here the BOSH-URL in the form: http(s)://domain:port/path.").' '.
                    t('If you enter an open BOSH-Server, you can connect to many XMPP-Servers.').' '.
                    t('If it is closed, you have to specify the corresponding Server on the next page.').' '.
                    t('If you are unsure about this config option visit the wiki');
                '</p>';
                    
        if(!$this->testBosh($this->_conf['boshUrl'])) {
            $html .= '
                <div class="message error">'.
                    t('Your Bosh URL is not reachable').'
                </div>';
        }
                    
        $html .= '
                    <div class="element">
                        <label for="boshUrl">'.t("Bosh URL").'</label>
                        <input type="text" id="boshUrl" name="boshUrl" value="'.$this->_conf['boshUrl'].'"/>
                    </div>';
                    
        $html .= '  
                    <div class="clear"></div>
                    <a class="button icon yes color green" style="float: right;" onclick="'.$submit.'">'.t('Submit').'</a>';

        $html .= '
            </fieldset>';
        
        $html .= '
            <fieldset>
                <legend>'.t("Whitelist - XMPP Server").'</legend>
                    <div class="clear"></div>';                    
        
        $html .= '<p>'.
                    t("If you want to specify a list of authorized XMPP servers on your Movim pod and forbid the connection on all the others please put their domain name here, with comma (ex: movim.eu,jabber.fr)").
                '</p>'.
                '<p>'.
                    t("Leave this field blank if you allow the access to all the XMPP accounts.").
                '</p>';
                
        $html .= '
                    <div class="element large">
                            <label for="xmppWhiteList">'.t("List of whitelisted XMPP servers").'</label>
                            <input type="text" name="xmppWhiteList" id="xmppWhiteList" value="'.$this->_conf['xmppWhiteList'].'" />
                    </div>';

        $html .= '  
                    <div class="clear"></div>
                    <a class="button icon yes color green" style="float: right;" onclick="'.$submit.'">'.t('Submit').'</a>';

        $html .= '
            </fieldset>';
            
        $html .= '
            <fieldset>
                <legend>'.t("Administration Credential").'</legend>';
                    
        if($this->_conf['user'] == 'admin' && $this->_conf['pass'] == sha1('password')) {
            $html .= '
                <div class="message error">'.
                    t('Change the default username/password').'
                </div>';
        }
            
        $html .= '
                    <div class="element" >
                        <label for="username">'.t("Username").'</label>
                        <input type="text" id="user" name="user" value="'.$this->_conf['user'].'"/>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="element">
                        <label for="pass">'.t("Password").'</label>
                        <input type="password" id="pass" name="pass" value=""/>
                    </div>			                
                    <div class="element">
                        <label for="repass">'.t("Retype password").'</label>
                        <input type="password" id="repass" name="repass" value=""/>
                    </div>	';

        $html .= '  
                    <div class="clear"></div>
                    <a class="button icon yes color green" style="float: right;" onclick="'.$submit.'">'.t('Submit').'</a>';
                    
        $html .= '
            </fieldset><br />';
            
        $html .= '
        </form>';
            
        return $html;
    }

    function build()
    {
    ?>
        <div id="admin" style="margin: 1.5em;">
            <?php echo $this->prepareAdmin(); ?>
        </div>
    <?php 
    }
}
