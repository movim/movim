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
    function WidgetLoad()
    {
        $this->addcss('media.css');
        $this->addjs('media.js');
        
        if(!is_dir($this->user->userdir) && $this->user->userdir != '') {
            mkdir($this->user->userdir);
            touch($this->user->userdir.'index.html');
        }
            
        $this->registerEvent('media', 'onMediaUploaded');
    }
    
    function ajaxRefreshMedia()
    {
        $html = $this->mainFolder();
        RPC::call('movim_fill', 'media', $html);
        RPC::commit();
    }
    
    function ajaxDeleteItem($name)
    {
        unlink($this->user->userdir.'thumb_'.$name);
        unlink($this->user->userdir.'medium_'.$name);
        unlink($this->user->userdir.$name);
        
        $this->ajaxRefreshMedia();
    }
    
    function listFiles()
    {
        $html = '<ul class="thumb">';

        foreach($this->user->getDir() as $key => $value) {
            $html .= 
                    '<li style="background-image: url('.$value['thumb'].');">
                        <a href="'.Route::urlize('media', $key).'">
                        </a>
                            <div 
                                class="remove" 
                                onclick="'.
                                    $this->genCallAjax(
                                        'ajaxDeleteItem', 
                                        "'".$key."'"
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
            
            $exif .= '<li><span>'.t('Original').'</span><a target="_blank" href="'.$this->user->useruri.$f.'">'.t('Link').'</a></li>';
                
            $html = '
                <div class="viewer">
                    <img src="'.$this->user->useruri.'medium_'.$f.'"/>
                    
                    <div class="exif">
                        <ul>
                            '.$exif.'
                        </ul>
                    </div>
                </div>';
                
            return $html;
        }
    }
    
    function build()
    {
        $refresh = $this->genCallAjax('ajaxRefreshMedia');
    ?>
        <script type="text/javascript">
            function refreshMedia() {
                <?php echo $refresh; ?>
            }
        </script>
    <?php
        if(isset($_GET['f'])) {
    ?>
        <div class="tabelem" title="<?php echo t('Viewer'); ?>" id="viewer">
            <?php echo $this->pictureViewer($_GET['f']); ?>
        </div>
    <?php
        }
    ?>
        <div class="tabelem" title="<?php echo t('Media'); ?>" id="media">    
            <?php echo $this->mainFolder(); ?>
            <div class="clear"></div>
        </div>
    <?php
    }

}
