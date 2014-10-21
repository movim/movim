<?php

/**
 * @package Widgets
 *
 * @file Media.php
 * This file is part of MOVIM.
 *
 * @brief The media manager.
 *
 * @author Timothée Jaussoin <edhelas@gmail.com>
 *
 * @version 1.0
 * @date 07 December 2011
 *
 * Copyright (C)2010 MOVIM project
 *
 * See COPYING for licensing information.
 */

class Media extends WidgetBase {
    function load()
    {
        $this->addcss('media.css');
        $this->addjs('media.js');
        
        if(!is_dir($this->user->userdir) && $this->user->userdir != '') {
            mkdir($this->user->userdir);
            touch($this->user->userdir.'index.html');
        }
            
        $this->registerEvent('media', 'onMediaUploaded');
    }
    
    function display() {
        $this->view->assign('refresh', $this->genCallAjax('ajaxRefreshMedia'));
    }
    
    function ajaxRefreshMedia()
    {
        $html = $this->mainFolder();
        RPC::call('movim_fill', 'media', $html);
        RPC::commit();
    }
    
    function ajaxDeleteItem($name)
    {
        unlink($this->user->userdir.$name);
        
        $this->ajaxRefreshMedia();
    }
    
    function listFiles()
    {
        $html = '<ul class="thumb">';

        foreach($this->user->getDir() as $file) {
            $p = new \Picture;

            // Just to prevent issue when you update from an old Movim version
            if($p->get($this->user->userdir.$file, 300) == false) {
                    $p->fromPath($this->user->userdir.$file);
                    $p->set($this->user->userdir.$file);
            }
            
            $html .= 
                    '<li style="background-image: url('.$p->get($this->user->userdir.$file, 300).');">
                        <a href="'.Route::urlize('media', $file).'">
                        </a>
                            <div 
                                class="remove" 
                                onclick="'.
                                    $this->genCallAjax(
                                        'ajaxDeleteItem', 
                                        "'".$file."'"
                                    ).'">
                                x
                            </div>
                    </li>
                    '; 
        }
        
        $html .= '</ul>';
        
        return $html;
    }
    
    function mainFolder()
    {
        $percent = number_format(($this->user->dirSize()/$this->user->sizelimit)*100, 2);
    
        $html = 
            $this->listFiles().'                
            <span class="size"
                title="'.sizeToCleanSize($this->user->dirSize()).' '.t('on').' '.sizeToCleanSize($this->user->sizelimit).'"
            >'.
                $percent.'%
            </span>';
        
        return $html;
    }
    
    function pictureViewer($f)
    {        
        if(file_exists($this->user->userdir.$f) && getimagesize($this->user->userdir.$f) != 0) {
        
            $er = @exif_read_data($this->user->userdir.$f);

            $exif = '';
                
            if($er) {            
                if(isset($er['FileName']))
                    $exif .= '
                        <li>
                            <span>'.$this->__('media.name').'</span>'.
                            $er['FileName'].'
                        </li>';
                if(isset($er['COMPUTED']['Width']) && isset($er['COMPUTED']['Height']))
                    $exif .= '
                        <li>
                            <span>'.$this->__('media.resolution').'</span>'.
                            $er['COMPUTED']['Width'].'x'.$er['COMPUTED']['Height'].'
                        </li>';
                if(isset($er['FileSize']))
                    $exif .= '
                        <li>
                            <span>'.$this->__('media.size').'</span>'.
                            sizeToCleanSize($er['FileSize']).'
                        </li>';
                if(isset($er['DateTime']))
                    $exif .= '
                        <li>
                            <span>'.$this->__('media.date').'</span>'.
                            prepareDate(strtotime($er['DateTime'])).'
                        </li>';
                if(isset($er['ISOSpeedRatings']))
                    $exif .= '
                        <li>
                            <span>'.$this->__('media.iso').'</span>'.
                            $er['ISOSpeedRatings'].'
                        </li>';
                if(isset($er['Model']))
                    $exif .= '
                        <li>
                            <span>'.$this->__('media.camera').'</span>'.
                            $er['Model'].'
                        </li>';
                if(isset($er['Artist']))
                    $exif .= '
                        <li>
                            <span>'.$this->__('media.artist').'</span>'.
                            $er['Artist'].'
                        </li>';
            }
            
            $exif .= '
                <li>
                    <span>'.$this->__('media.original').'</span>
                    <a target="_blank" href="'.$this->user->useruri.$f.'">'.
                        $this->__('media.link').'
                    </a>
                </li>';
                
            $html = '
                <div class="viewer">
                    <img src="'.$this->user->useruri.$f.'" style="max-width: '.$er['COMPUTED']['Width'].'px"/>
                    
                    <div class="exif">
                        <ul>
                            '.$exif.'
                        </ul>
                    </div>
                </div>';
                
            return $html;
        }
    }

}
