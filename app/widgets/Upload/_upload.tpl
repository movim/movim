<section id="upload">
    <h3>{$c->__('upload.title')}</h3>
    <ul class="list thick">
        <li>
            <span class="primary icon">
                <i class="material-icons">attach_file</i>
            </span>
            <div>
                <p>{$c->__('upload.choose')}</p>
                {if="isset($service->description)"}
                    <p>{$c->__('upload.max_size', sizeToCleanSize($service->description))}</p>
                {/if}
                <p>
                    <input type="file" id="file" onchange="Upload.preview()"/>
                </p>
            </div>
        </li>
    </ul>
    <ul class="list">
        <div class="drop">
            <img class="preview_picture transparent" />
            <li>
                <span class="primary icon gray">
                    <i class="material-icons on_desktop">system_update_alt</i>
                    <i class="material-icons on_mobile">photo_size_select_large</i>
                </span>
                <div>
                    <p class="on_desktop">
                        {$c->__('upload.drag_drop')}
                    </p>
                    <p>
                        {$c->__('upload.info')}
                    </p>
                </div>
            </li>
            <li class="file">
                <div>
                    <p class="name line center"></p>
                    <p class="desc line center"></p>
                </div>
                <span class="primary active bubble color icon green">
                    <i class="material-icons">gesture</i>
                </span>
            </li>
        </div>
    </ul>
</section>
<div class="no_bar">
    <button onclick="Dialog_ajaxClear(); Upload.abort();" class="button flat">
        {$c->__('button.close')}
    </button>
    <button id="upload_button" onclick="Upload.init();" class="button flat disabled">
        {$c->__('button.upload')}
    </button>
</div>
