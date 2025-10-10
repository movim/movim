<section id="upload">
    <h3>{$c->__('upload.title')}</h3>
    <ul class="list thick">
        <li>
            <span class="control icon active divided" onclick="Upload.openImage()">
                <i class="material-symbols">image</i>
            </span>
            <span class="control icon active" onclick="Upload.openFile()">
                <i class="material-symbols">insert_drive_file</i>
            </span>
            <div>
                <p>{$c->__('upload.choose')}</p>
                {if="isset($service->description)"}
                    <p class="limit" data-limit="{$service->description}">{$c->__('upload.max_size', humanSize($service->description))}</p>
                {/if}
                <input type="file" id="file" onchange="Upload.preview()"/>
                <input type="file" id="image" accept="image/*" onchange="Upload.preview()"/>
            </div>
        </li>
    </ul>
    <ul class="list">
        <div class="drop">
            <img class="preview_picture transparent" />
            <li>
                <span class="primary icon gray">
                    <i class="material-symbols on_desktop">system_update_alt</i>
                    <i class="material-symbols on_mobile">photo_size_select_large</i>
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
                    <i class="material-symbols">gesture</i>
                </span>
            </li>
        </div>
    </ul>
</section>
<footer>
    <ul id="upload_progress" class="list">
        <li>
            <span class="primary icon compress">
                <i class="material-symbols"></i>
            </span>
            <span class="primary icon upload">
                <i class="material-symbols"></i>
            </span>
            <div>
                <p class="line"></p>
            </div>
        </li>
    </ul>
    <button onclick="Dialog_ajaxClear(); Upload.abort();" class="button flat">
        {$c->__('button.close')}
    </button>
    <button id="upload_button" onclick="Upload.init();" class="button color disabled">
        {$c->__('button.upload')}
    </button>
</footer>
