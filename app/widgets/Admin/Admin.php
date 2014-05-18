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
            <input 
                type="submit" 
                class="button icon yes color green oppose" 
                value="'.__('button.submit').'"/>';
        
        $html = '
            <fieldset>
                <legend>'.$this->__('admin.compatibility').'</legend>
                <div class="clear"></div>';

                $html .= 
                    '<p>'.
                        $this->__('compatibility.info').
                    '</p><br />';
                    
                $html .= '                
                    <div class="'.$this->isValid((version_compare(PHP_VERSION, '5.3.0') >= 0)).'">
                        '.$this->__('compatibility.php', PHP_VERSION).'
                    </div>
                    <div class="'.$this->isValid(extension_loaded('curl')).'">
                        '.$this->__('compatibility.curl').'
                    </div>
                    <div class="'.$this->isValid(extension_loaded('gd')).'">
                        '.$this->__('compatibility.gd').'
                    </div>
                    <div class="'.$this->isValid(extension_loaded('SimpleXml')).'">
                        '.$this->__('compatibility.simplexml').'
                    </div>
                    <div class="'.$this->isValid($this->testDir(DOCUMENT_ROOT)).'">
                        '.$this->__('compatibility.rights').'
                    </div>
                    <div class="'.$this->isValid(extension_loaded('OpenSSL')).'">
                        '.$this->__('compatibility.openssl').'
                    </div>
                    
            </fieldset>

            <fieldset>
                <legend>'.$this->__('compatibility.rewrite').'</legend>
                    <div class="clear"></div>
                    <div class="'.$this->isValid($_SERVER['HTTP_MOD_REWRITE']).'">
                        '.$this->__('compatibility.rewrite').'
                    </div>';
            
        $html .= '
            </fieldset>';
            
        return $html;
    }
    
    function prepareAdminGen() {
        $html = '';
        
        $html .= '
            <fieldset>
                    <legend>'.$this->__('admin.general').'</legend>
                    <div class="element">
                        <label for="movim" >'.$this->__('general.theme').'</label>
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
                        <label for="da">'.$this->__('general.language').'</label>
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
                        <label for="da">'.$this->__('general.environment').'</label>
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
                            <label for="sizeLimit">'.$this->__('general.limit').'</label>
                            <input type="text" name="sizeLimit" id="sizeLimit" value="'.$this->_conf['sizeLimit'].'" />
                    </div>';

        $logopts = array(
            0 => t('Empty'),
            1 => t('Syslog'),
            2 => t('Syslog and Files')
        );
        
        $html .= '
                    <div class="element">
                        <label for="logLevel">'.$this->__('general.log_verbosity').'</label>
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
                        <label for="timezone">'.$this->__('general.timezone').'</label>
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
                <legend>'.$this->__('bosh.title').'</legend>
                    <div class="clear"></div>';
                    
        $html .= '<p>'.
                    $this->__('bosh.info1').' '.
                    $this->__('bosh.info2').' '.
                    $this->__('bosh.info3').' '.
                    $this->__('bosh.info4', '<a href="http://wiki.movim.eu/install">', '</a>');
                '</p>';
                    
        if(!$this->testBosh($this->_conf['boshUrl'])) {
            $html .= '
                <div class="message error">'.
                    t('bosh.not_recheable').'
                </div>';
        }
                    
        $html .= '
                    <div class="element">
                        <label for="boshUrl">'.$this->__('bosh.label').'</label>
                        <input type="text" id="boshUrl" name="boshUrl" value="'.$this->_conf['boshUrl'].'"/>
                    </div>';
                    
        $html .= $this->_validatebutton;

        $html .= '
            </fieldset>';
        
        $html .= '
            <fieldset>
                <legend>'.$this->__('whitelist.title').'</legend>
                    <div class="clear"></div>';                    
        
        $html .= 
            '<p>'.$this->__('whitelist.info1').'</p>'.
            '<p>'.$this->__('whitelist.info2').'</p>';
                
        $html .= '
                    <div class="element large">
                            <label for="xmppWhiteList">'.$this->__('whitelist.label').'</label>
                            <input type="text" name="xmppWhiteList" id="xmppWhiteList" value="'.$this->_conf['xmppWhiteList'].'" />
                    </div>';

        $html .= $this->_validatebutton;

        $html .= '
            </fieldset>';
            
        $html .= '
            <fieldset>
                <legend>'.$this->__('information.title').'</legend>
                    <div class="clear"></div>';                    
        
        $html .= '<p>'.
                    $this->__('information.info1').
                '</p>'.
                '<p>'.
                    $this->__('information.info2').
                '</p>';
                
        $html .= '
                    <div class="element large">
                            <label for="info">'.$this->__('information.label').'</label>
                            <textarea type="text" name="info" id="info" />'.$this->_conf['info'].'</textarea>
                    </div>';

        $html .= $this->_validatebutton;

        $html .= '
            </fieldset>';
            
        $html .= '
            <fieldset>
                <legend>'.$this->__('credentials.title').'</legend>';
                    
        if($this->_conf['user'] == 'admin' || $this->_conf['pass'] == sha1('password')) {
            $html .= '
                <div class="message error">'.
                    $this->__('credentials.info').'
                </div>';
        }
            
        $html .= '
                    <div class="element" >
                        <label for="username">'.$this->__('credentials.username').'</label>
                        <input type="text" id="user" name="user" value="'.$this->_conf['user'].'"/>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="element">
                        <label for="pass">'.$this->__('credentials.password').'</label>
                        <input type="password" id="pass" name="pass" value=""/>
                    </div>                            
                    <div class="element">
                        <label for="repass">'.$this->__('credentials.re_password').'</label>
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
