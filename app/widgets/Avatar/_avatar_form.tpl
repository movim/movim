<form name="avatarform" id="avatarform">
    <fieldset>
        <legend>{$c->__('page.avatar')}</legend>
        <div class="element">
            <label for="avatar">{$c->__('page.avatar')}</label>
                <img id="vCardPhotoPreview" src="data:image/jpeg;base64,{$photobin}">
            <br /><span id="picturesize" class="clean"></span><br /><br />
            
            <input type="file" onchange="vCardImageLoad(this.files);">

            <input type="hidden" name="photobin"  value="{$photobin}"/>
        </div>

        <div class="element" id="camdiv">
            <label for="url">{$c->__('avatar.webcam')}</label>
            <video id="runningcam" class="squares" autoplay></video>
            <canvas style="display:none;"></canvas>
            
            <a 
                id="shoot" 
                class="button icon preview color green" 
                onclick="return false;">
                {$c->__('avatar.cheese')}
            </a>
            <a
                id="capture" 
                class="button icon image color purple" 
                onclick="
                    showVideo();
                    return false;">
                {$c->__('avatar.snapshot')}
            </a>
        </div>
    </fieldset>

    <a
        onclick="
            {$submit}
            movim_button_save('#avatarvalidate');
            this.value = '{$c->t('Submitting')}'; 
            this.className='button color orange icon loading inactive';" 
        class="button icon color green yes"
        id="avatarvalidate"
        style="float: right;"
        >{$c->__('button.submit')}</a>
</form>
