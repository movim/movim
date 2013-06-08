<?php

/**
 * @package Widgets
 *
 * @file MediaUpload.php
 * This file is part of MOVIM.
 *
 * @brief The media upload.
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

class MediaUpload extends WidgetBase {
    function WidgetLoad()
    {
        $this->addcss('mediaupload.css');
    }
    
    function build()
    {        
        if($this->user->dirSize() < $this->user->sizelimit) {
        ?>
        <div class="tabelem padded" title="<?php echo t('Upload'); ?>" id="mediaupload">      
            <form id="upload_form" enctype="multipart/form-data" method="post" action="upload.php">
                <fieldset>
                    <div class="element">
                        <label for="image_file"><?php echo t('Please select image file'); ?></label>
                        <input type="file" name="image_file" id="image_file" onchange="fileSelected();" />
                    </div>
        
                    <img id="preview" />
                    
                    <div id="fileinfo">
                        <div id="filename"></div>
                        <div id="filesize"></div>
                        <div id="filetype"></div>
                        <div id="filedim"></div>
                    </div>
                    
                    <div class="clear"></div>
                    
                    <div id="progress_info">
                        <div id="progress"></div>
                        <div id="progress_percent"></div>
                        <div class="clear_both"></div>
                        <div>
                            <div id="speed"></div>
                            <div id="remaining"></div>
                            <div id="b_transfered"></div>
                            <div class="clear_both"></div>
                        </div>
                        <div id="upload_response"></div>
                    </div>
                
                </fieldset>

                <div class="clear"></div>
                <a id="uploadbutton" class="button icon color green upload" onclick="startUploading()" /><?php echo t('Upload'); ?></a>
                    
                <div class="message info" id="error">
                    <?php echo t('You should select valid image files only!'); ?>
                </div>
                <div class="message error" id="error2">
                    <?php echo t('An error occurred while uploading the file'); ?>
                </div>
                <div class="message info" id="abort">
                    <?php echo t('The upload has been canceled by the user or the browser dropped the connection'); ?>
                </div>
                <div class="message info" id="warnsize">
                    <?php echo t("Your file is very big. We can't accept it. Please select a smaller file"); ?>
                </div>
                
            </form>
        </div>
        <?php        
        }
    }
}
