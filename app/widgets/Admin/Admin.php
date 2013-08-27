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
    private $_validatebutton;
    
    function WidgetLoad()
    {
        $this->addjs('admin.js');
        $this->_conf = \system\Conf::getServerConf();
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
        if(!file_exists(DOCUMENT_ROOT.'/cache') && !@mkdir(DOCUMENT_ROOT.'/cache')) {
            echo t("Couldn't create directory '%s'.", 'cache');
            return false;
        }
        
        if(!file_exists(DOCUMENT_ROOT.'/log') && !@mkdir(DOCUMENT_ROOT.'/log')) {
            echo t("Couldn't create directory '%s'.", 'log');
            return false;
        }
        
        if(!file_exists(DOCUMENT_ROOT.'/config') && !@mkdir(DOCUMENT_ROOT.'/config')) {
            echo t("Couldn't create directory '%s'.", 'config');
            return false;
        }
        
        if(!file_exists(DOCUMENT_ROOT.'/users') && !@mkdir(DOCUMENT_ROOT.'/users')) {
            echo t("Couldn't create directory '%s'.", 'users');
            return false;
        } else
            touch(DOCUMENT_ROOT.'/users/index.html');
    }
    
    private function listThemes()
    {
        $dir = opendir(DOCUMENT_ROOT.'/themes');
        $themes = array();

        while($theme = readdir($dir)) {
            if(preg_match('/^\.+$/', $theme)
            || !is_dir(DOCUMENT_ROOT.'/themes/'.$theme)) {
                continue;
            }

            $themes[$theme] = $theme;
        }

        return $themes;
    }
    
    private function listLangs()
    {
        $dir = opendir(DOCUMENT_ROOT.'/locales');
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

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        
        // We put a short timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); 

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
        
        if($form['repass'] == '' || !isset($form['repass']))
            $form['pass'] = $this->_conf['pass'];
        elseif($form['pass'] == $form['repass']) {
            unset($form['repass']);
            $form['pass'] = sha1($form['pass']);
        }
        
        foreach($this->_conf as $key => $value) {
            if(isset($form[$key]))
                $this->_conf[$key] = $form[$key];
        }

        \system\Conf::saveConfFile($this->_conf);
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
    
    private function prepareAdminComp()
    {
            
        if($this->testDir(DOCUMENT_ROOT))
            $this->createDirs();
            
        $submit = $this->genCallAjax('ajaxAdminSubmit', "movim_parse_form('admin')")
            ."this.className='button color orange icon loading'; setTimeout(function() {location.reload(false)}, 2000);";
            
        $this->_validatebutton = '
            <div class="clear"></div>
            <a class="button icon yes color green" style="float: right;" onclick="'.$submit.'">'.t('Submit').'</a>';
        
        $html = '
        <form name="admin" id="adminform">';
        
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
                    <div class="'.$this->isValid($this->testDir(DOCUMENT_ROOT)).'">
                        '.t('Read and write rights for the webserver in Movim\'s root directory').'
                    </div>
                    <div class="'.$this->isValid(extension_loaded('OpenSSL')).'">
                        '.t('OpenSSL').'
                    </div>
                    
            </fieldset>

            <fieldset>
                <legend>'.t('URL Rewriting support').'</legend>
                    <div class="clear"></div>
                    <div class="'.$this->isValid($_SERVER['HTTP_MOD_REWRITE']).'">
                        '.t('URL Rewriting support').'
                    </div>';
            
        $html .= '
            </fieldset>';
            
        return $html;
    }
    
    function prepareAdminGen() {
        $html = '';
        
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
                        
        $env = array(
            'development' => 'Development',
            'production'  => 'Production');
                        
        $html .= '
                    <div class="element">
                        <label for="da">'.t('Environment').'</label>
                            <div class="select">
                                <select id="environment" name="environment">';
                                    
                                    foreach($env as $key => $value) {
                                        if((string)$this->_conf['environment'] == $key)
                                            $sel = 'selected="selected"';
                                        else
                                            $sel = '';
                                                                            
                                        $html .= '
                                            <option value="'.$key.'" '.$sel.'>'.$value.'</option>';
                                    }
                    
        $html .= '              </select>
                            </div>
                        </div>';
        /*                
        $html .= '
                    <div class="element">
                            <label for="maxUsers">'.t('Maximum population').'</label>
                            <input type="text" name="maxUsers" id="maxUsers" value="'.$this->_conf['maxUsers'].'" />
                    </div>';
        */
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
                        <label for="logLevel">'.t("Log verbosity").'</label>
                        <div class="select">
                            <select id="logLevel" name="logLevel">';
                                foreach($logopts as $lognum => $text) {
                                    if($this->_conf['logLevel'] == $lognum)
                                        $sel = 'selected="selected"';
                                    else
                                        $sel = '';

                                $html .= '
                                    <option value="'.$lognum.'" '.$sel.'>'.
                                        $text.'
                                    </option>';
                                }
        $html .= '          </select>
                        </div>
                    </div>';
                    
        $timezones = getTimezoneList();
                    
        $html .= '
                    <div class="element">
                        <label for="timezone">'.t("Server Timezone").'</label>
                        <div class="select">
                            <select id="timezone" name="timezone">';
                                foreach($timezones as $key => $value) {
                                    if($this->_conf['timezone'] == $key)
                                        $sel = 'selected="selected"';
                                    else
                                        $sel = '';
                                        
                                $html .= '
                                    <option value="'.$key.'" '.$sel.'>'.
                                        $key.' ('.number_format($value, 2).')
                                    </option>';
                                }
        $html .= '          </select>
                        </div>
                        <br /><br />
                        <span class="dTimezone">'.date('l jS \of F Y h:i:s A').'</span>
                    </div>';
                    
        $html .= $this->_validatebutton;
        
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
                    t('If you are unsure about this config option visit the %swiki%s', '<a href="http://wiki.movim.eu/install">', '</a>');
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
                    
        $html .= $this->_validatebutton;

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

        $html .= $this->_validatebutton;

        $html .= '
            </fieldset>';
            
        $html .= '
            <fieldset>
                <legend>'.t("Information Message").'</legend>
                    <div class="clear"></div>';                    
        
        $html .= '<p>'.
                    t("This message will be displayed on the login page").
                '</p>'.
                '<p>'.
                    t("Leave this field blank if you don't want to show any message.").
                '</p>';
                
        $html .= '
                    <div class="element large">
                            <label for="info">'.t("Information Message").'</label>
                            <textarea type="text" name="info" id="info" />'.$this->_conf['info'].'</textarea>
                    </div>';

        $html .= $this->_validatebutton;

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
                    </div>    ';

        $html .= $this->_validatebutton;
                    
        $html .= '
            </fieldset><br />';
        
        return $html;
    }
    
    function prepareAdminDB() {
        $html = '';
        
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
                    $dbrecreate = $this->genCallAjax('ajaxRecreateDatabase')
                        ."  this.className='button color orange icon loading'; 
                            setTimeout(function() {location.reload(false)}, 2000);";
                    
                    $html .= '
                    <div class="element">
                        <label for="db">'.t('Recreate the database').'</label>
                        <a class="button icon loading color red" onclick="'.$dbrecreate.'">'.t('Recreate').'</a>
                    </div>
                    
                    <div class="message error">
                        '.t('This button will clear and recreate the Movim database.').'
                    </div>
                    ';
                }

        $html .= '
                    <div class="element">
                            <label for="dbType">'.t('Database Type').'</label>
                            <input type="text" disabled="true" name="dbType" id="dbType" value="'.$this->_conf['dbType'].'" />
                    </div>';
                    
        $html .= '
                    <div class="element">
                            <label for="dbUsername">'.t('Username').'</label>
                            <input type="text" name="dbUsername" id="dbUsername" value="'.$this->_conf['dbUsername'].'" />
                    </div>';
                    
        $html .= '
                    <div class="element">
                            <label for="dbPassword">'.t('Password').'</label>
                            <input type="password" name="dbPassword" id="dbPassword" value="'.$this->_conf['dbPassword'].'" />
                    </div>';
                    
        $html .= '
                    <div class="element">
                            <label for="dbHost">'.t('Host').'</label>
                            <input type="text" name="dbHost" id="dbHost" value="'.$this->_conf['dbHost'].'" />
                    </div>';
                    
        $html .= '
                    <div class="element">
                            <label for="dbPort">'.t('Port').'</label>
                            <input type="text" name="dbPort" id="dbPort" value="'.$this->_conf['dbPort'].'" />
                    </div>';
                    
        $html .= '
                    <div class="element">
                            <label for="dbName">'.t('Database Name').'</label>
                            <input type="text" name="dbName" id="dbName" value="'.$this->_conf['dbName'].'" />
                    </div>';
        
        $html .= $this->_validatebutton;
            
        $html .= '
            </fieldset>';
            
            $html .= '
        </form>';
            
        return $html;
    }

    function build()
    {
    ?>
        <div id="admincomp" class="tabelem padded" title="<?php echo t("Compatibility Check"); ?>">
            <?php echo $this->prepareAdminComp(); ?>
        </div>
        <div id="admingen" class="tabelem padded" title="<?php echo t('General Settings'); ?>">
			<?php echo $this->prepareAdminGen(); ?>
        </div>
        <div id="admindb" class="tabelem padded" title="<?php echo t("Database Settings") ?>">
			<?php echo $this->prepareAdminDB(); ?>
        </div>
    <?php 
    }
}
