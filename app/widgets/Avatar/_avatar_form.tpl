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
            <label for="url"><i class="fa fa-camera"></i> {$c->__('avatar.webcam')}</label>
            <video id="runningcam" class="squares" autoplay></video>
            <canvas style="display:none;"></canvas>
            
            <a 
                id="shoot" 
                class="button color green" 
                onclick="return false;">
                <i class="fa fa-smile-o"></i> {$c->__('avatar.cheese')}
            </a>
            <a
                id="capture" 
                class="button color purple" 
                onclick="
                    showVideo();
                    return false;">
                <i class="fa fa-smile-o"></i> {$c->__('avatar.snapshot')}
            </a>
        </div>
    </fieldset>

    <a
        onclick="
            {$submit}
            movim_button_save('#avatarvalidate');
            this.value = '{$c->t('Submitting')}'; 
            this.className='button color orange icon loading inactive';" 
        class="button color green oppose"
        id="avatarvalidate"
        ><i class="fa fa-check"></i> {$c->__('button.submit')}</a>
</form>
