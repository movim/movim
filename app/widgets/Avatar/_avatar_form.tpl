<form name="avatarform" id="avatarform">
    <h3>{$c->__('page.avatar')}</h3>
    <div class="block">        
        <input type="file" onchange="vCardImageLoad(this.files);">
        <label for="avatar">{$c->__('page.avatar')}</label>
        <input type="hidden" name="photobin"  value="{$photobin}"/>
    </div>

    <div class="block" id="result">
        <img id="vCardPhotoPreview" src="data:image/jpeg;base64,{$photobin}">
        <span id="picturesize" class="clean"></span>
    </div>

    <div id="camdiv" class="block">
        <video id="runningcam" class="squares" autoplay></video>
        <canvas style="display:none;"></canvas>
        
        <a 
            id="shoot" 
            class="button flat oppose" 
            onclick="return false;">
            {$c->__('avatar.cheese')}
        </a>
        <a
            id="capture" 
            class="button flat" 
            onclick="
                showVideo();
                return false;">
            {$c->__('avatar.snapshot')}
        </a>
        <label for="url">{$c->__('avatar.webcam')}</label>
    </div>

    <div class="clear"></div>

    <a
        onclick="
            {$submit}
            movim_button_save('#avatarvalidate');
            this.value = '{$c->__('button.submitting')}'; 
            this.className='button color inactive oppose';" 
        class="button color oppose"
        id="avatarvalidate"
        >{$c->__('button.submit')}</a>
</form>
