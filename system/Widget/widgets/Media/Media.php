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

    private $_userdir;
    private $_useruri;
    public $_sizelimit;

    function WidgetLoad()
    {
        $this->addcss('media.css');
        $this->addjs('media.js');
        
        $this->_sizelimit = (int)Conf::getServerConfElement('sizeLimit');

        $this->_userdir = BASE_PATH.'users/'.$this->user->getLogin().'/';
        $this->_useruri = BASE_URI.'users/'.$this->user->getLogin().'/';
        
        if(!is_dir($this->_userdir))
            mkdir($this->_userdir);
    }
    
    function dirSize()
    {
        $sum = 0;
        
        foreach(scandir($this->_userdir) as $s) {
            if($s != '.' && $s != '..')
                $sum = $sum + filesize($this->_userdir.$s);
        }
        
        return $sum;
    }
    
    function listFiles()
    {
        $html = '<ul class="thumb">';
        
        foreach(scandir($this->_userdir) as $s) {
            if(
                $s != '.' && 
                $s != '..' && 
                substr($s, 0, 6) != 'thumb_' &&
                substr($s, 0, 7) != 'medium_')
                $html .= 
                    '<a href="?q=media&f='.$s.'">
                        <li style="background-image: url('.$this->_useruri.'thumb_'.$s.');"></li>
                    </a>';           
        }
        
        $html .= '</ul>';
        
        return $html;
    }
    
    function mainFolder()
    {
    $percent = number_format(($this->dirSize()/$this->_sizelimit)*100, 2);
    ?>
    <div class="tabelem" title="<?php echo t('Media'); ?>" id="media">        
        <?php echo $this->listFiles(); ?>
        
        <span class="size">
            <?php 
                echo sizeToCleanSize($this->dirSize()).' '.t('on').' '.sizeToCleanSize($this->_sizelimit); 
                echo ' - ';
                echo $percent.'%';
            ?>
        </span>
    </div>
    <?php
    }
    
    function pictureViewer($f)
    {
        //var_dump(exif_read_data($this->_userdir.$f));
        
        if(file_exists($this->_userdir.$f) && getimagesize($this->_userdir.$f) != 0) {
        
            $er = @exif_read_data($this->_userdir.$f);
            

            $exif = '';
                
            if($er) {            
                if(isset($er['FileName']))
                    $exif .= '<li><span>'.t('Name').'</span>'.$er['FileName'].'</li>';
                if(isset($er['COMPUTED']['Width']) && isset($er['COMPUTED']['Height']))
                    $exif .= '<li><span>'.t('Resolution').'</span>'.$er['COMPUTED']['Width'].'x'.$er['COMPUTED']['Height'].'</li>';
                if(isset($er['FileSize']))
                    $exif .= '<li><span>'.t('Size').'</span>'.sizeToCleanSize($er['FileSize']).'</li>';
                if(isset($er['DateTime']))
                    $exif .= '<li><span>'.t('Date').'</span>'.prepareDate(strtotime($er['DateTime'])).'</li>';
                if(isset($er['ISOSpeedRatings']))
                    $exif .= '<li><span>'.t('ISO').'</span>'.$er['ISOSpeedRatings'].'</li>';
                if(isset($er['Model']))
                    $exif .= '<li><span>'.t('Camera').'</span>'.$er['Model'].'</li>';
                if(isset($er['Artist']))
                    $exif .= '<li><span>'.t('Artist').'</span>'.$er['Artist'].'</li>';
            }
            
            $exif .= '<li><span>'.t('Original').'</span><a target="_blank" href="'.$this->_useruri.$f.'">'.t('Link').'</a></li>';
                
        ?>
        <div class="tabelem" title="<?php echo t('Viewer'); ?>" id="viewer">
            <div class="viewer">
                <img src="<?php echo $this->_useruri.'medium_'.$f; ?>"/>
                
                <div class="exif">
                    <ul>
                        <?php echo $exif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    }
    
	function build()
	{
        if(!isset($_GET['f']))
            $this->mainFolder();
        else {
            $this->pictureViewer($_GET['f']);
            $this->mainFolder();
        }
    }

}
