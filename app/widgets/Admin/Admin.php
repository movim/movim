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
    
    function load()
    {
        $this->addjs('admin.js');
        $this->_conf = Conf::getServerConf();

        $this->saveConfig($_POST);
        $_POST = null;
    }

    private function saveConfig($form) {
        if($form['pass'] != '' && $form['repass'] != ''
        && $form['pass'] == $form['repass']) {
            unset($form['repass']);
            $form['pass'] = sha1($form['pass']);
        } else {
            $form['pass'] = $this->_conf['pass'];
        }
        
        foreach($this->_conf as $key => $value) {
            if(isset($form[$key]))
                $this->_conf[$key] = $form[$key];
        }

        Conf::saveConfFile($this->_conf);
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
        return loadLangArray();
    }
    
    function testBosh($url)
    {
        return requestURL($url, 2);
    }

    public function ajaxUpdateDatabase()
    {
        $md = \modl\Modl::getInstance();
        $md->check(true);
        RPC::call('movim_reload_this');
    }
    
    function prepareAdminComp()
    {            
        $this->_validatebutton = '
            <div class="clear"></div>
            <input type="submit" class="button icon yes color green" style="float: right;" value="'.t('Submit').'"/>';
        
        $html = '
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
                                <select id="defLang" name="defLang">
                                    <option value="en">English (default)</option>';
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

        $html .= '
                    <div class="element">
                            <label for="sizeLimit">'.t('User folder size limit (in bytes)').'</label>
                            <input type="text" name="sizeLimit" id="sizeLimit" value="'.$this->_conf['sizeLimit'].'" />
                    </div>';
        /*
        $logopts = array(
            0 => t('empty'),
            2 => t('terse'),
            4 => t('normal'),
            6 => t('talkative'),
            7 => t('ultimate'),
        );
        
        $default_log = 4;
        */

        $logopts = array(
            0 => t('Empty'),
            1 => t('Syslog'),
            2 => t('Syslog and Files')
        );
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

                                    if($this->_conf['timezone'] == $key) {
                                        $sel = 'selected="selected"';
                                    } else
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
                    
        if($this->_conf['user'] == 'admin' || $this->_conf['pass'] == sha1('password')) {
            $html .= '
                <div class="message error">'.
                    t('Change the default credentials admin/password').'
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
        $dbview = $this->tpl();
        
        $md = \modl\Modl::getInstance();
        $infos = $md->check();
        
        $errors = '';

        $dbview->assign('infos', $infos); 
        $dbview->assign('db_update', $this->genCallAjax('ajaxUpdateDatabase')
            ."this.className='button color orange icon loading'; setTimeout(function() {location.reload(false)}, 1000);");
        try {
            $md->connect();
        } catch(Exception $e) {
            $errors = $e->getMessage();
        }
        
        $dbview->assign('connected', $md->_connected);
        $dbview->assign('validatebutton', $this->_validatebutton);
        $dbview->assign('conf', $this->_conf);
        $dbview->assign('supported_db', $md->getSupportedDatabases());
        $dbview->assign('errors', $errors);

        $html = $dbview->draw('_admin_db', true);

        return $html;
    }
}
