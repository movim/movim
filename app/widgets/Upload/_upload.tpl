<section id="upload">
    <h3>{$c->__('upload.title')}</h3>
    <ul class="list thick">
        <li>
            <span class="primary icon bubble color green">
                <i class="material-icons">cloud_upload</i>
            </span>
            <p>{$c->__('upload.choose')}</p>
            <p>
                <input type="file" id="file" onchange="Upload.preview()"/>
            </p>
        </li>
        <img class="preview_picture" />
        <li>
            <span class="primary icon gray">
                <i class="material-icons">photo_size_select_large</i>
            </span>
            <p></p>
            <p class="normal">
                {$c->__('upload.info')}
            </p>
        </li>
    </ul>
</section>
<div>
    <button onclick="Dialog_ajaxClear(); Upload.abort();" class="button flat">
        {$c->__('button.close')}
    </button>
    <button onclick="Upload.init();" class="button flat">
        {$c->__('button.upload')}
    </button>
</div>
