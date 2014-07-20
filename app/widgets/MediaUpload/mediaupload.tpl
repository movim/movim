{if="$limit"}
    <div class="tabelem padded" title="{$c->__('button.upload')}" id="mediaupload">      
        <form id="upload_form" enctype="multipart/form-data" method="post" action="upload.php">
            <fieldset>
                <div class="element">
                    <label for="image_file"><i class="fa fa-file-image-o"></i> {$c->__('upload.info')}</label>
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
            <a id="uploadbutton" class="button color green" onclick="startUploading()" />
                <i class="fa fa-upload"></i> {$c->__('button.upload')}
            </a>
                
            <div class="message info" id="error">
                {$c->__('upload.image_only')}
            </div>
            <div class="message error" id="error2">
                {$c->__('upload.error')}
            </div>
            <div class="message info" id="abort">
                {$c->__('upload.abort')}
            </div>
            <div class="message info" id="warnsize">
                {$c->__('upload.size_limit')}
            </div>
            
        </form>
    </div>
{/if}
