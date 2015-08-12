<br />

<h3 class="block large">{$c->__('page.avatar')}</h3>

<div class="flex">
    <div id="preview" class="block">
        <div>
            <form name="avatarform" id="avatarform">
                <img src="data:image/jpeg;base64,{$photobin}">
                <input type="hidden" name="photobin" value="{$photobin}"/>
            </form>
        </div>
    </div>
    <div class="block">
        <ul class="thick divided">
            <li class="condensed">
                <span class="icon bubble color green">
                    <i class="zmdi zmdi-attachment-alt"></i>
                </span>
                <span>{$c->__('avatar.file')}</span>
                <p><input type="file" onchange="Avatar.file(this.files);"></p>
            </li>
            {if="isset($gravatar)"}
            <li class="condensed action">
                <div class="action">
                    <a
                        onclick="Avatar.preview('data:image/jpeg;base64,{$gravatar_bin}')"
                        class="button flat">
                        {$c->__('avatar.use_it')}
                    </a>
                </div>
                <span class="icon bubble color blue">
                    <img src="http://www.gravatar.com/avatar/{$gravatar->entry[0]->hash}?s=50" />
                </span>
                <span>Gravatar</span>
                <p>We found a Gravatar picture</p>
            </li>
            {/if}
        </ul>
    </div>
</div>
    <!--
    <div class="block">        
        <input type="file" onchange="vCardImageLoad(this.files);">
        <label for="avatar">{$c->__('page.avatar')}</label>
        <input type="hidden" name="photobin"  value="{$photobin}"/>
    </div>

    {if="isset($gravatar)"}
    <div class="block">
        <h4>Gravatar</h4>
        {$gravatar->entry[0]|var_dump}
        <img src="http://www.gravatar.com/avatar/{$gravatar->entry[0]->hash}?s=500" />
    </div>
    {/if}

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
    </div>-->

<div class="block large">
    <a
        onclick="
            {$submit}
            movim_button_save('#avatarvalidate');
            this.value = '{$c->__('button.submitting')}'; 
            this.className='button inactive oppose';" 
        class="button color oppose"
        id="avatarvalidate"
        >{$c->__('button.submit')}</a>
</div>
