<div class="flex">
    <div id="avatar_preview" class="block">
        <div>
            <form name="avatarform" id="avatarform">
                {if="isset($photobin) && $photobin != ''"}
                    <img class="avatar" src="data:image/jpeg;base64,{$photobin}">
                {else}
                    <img class="avatar" src="">
                {/if}
                <div class="placeholder">
                    <i class="material-icons">image</i>
                    <h1>{$c->__('avatar.missing')}</h1>
                </div>
                <input type="hidden" name="photobin" value="{$photobin}"/>
            </form>
        </div>
    </div>
    <div class="block">
        <ul class="list thick divided">
            <li>
                <span class="primary icon bubble color green">
                    <i class="material-icons">attach_file</i>
                </span>
                <div>
                    <p>{$c->__('avatar.file')}</p>
                    <p><input type="file" onchange="MovimAvatar.file(this.files, 'avatarform');"></p>
                </div>
            </li>
        </ul>
    </div>
</div>
    <!--
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
    </div>-->

<div class="block large">
    <button
        type="button"
        onclick="
            Avatar_ajaxSubmit(MovimUtils.formToJson('avatarform'));
            this.value = '{$c->__('button.submitting')}';
            this.className='button inactive oppose';"
        class="button color oppose"
        id="avatarvalidate"
        >{$c->__('button.submit')}</button>
</div>
