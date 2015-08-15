<section>
    <h3>{$c->__('upload.title')}</h3>
    <ul class="thick">
        <li class="condensed">
            <span class="icon bubble color green">
                <i class="zmdi zmdi-upload"></i>
            </span>
            <span>{$c->__('upload.choose')}</span>
            <p>
                <input type="file" id="file" />
            </p>
        </li>
    </ul>
</section>
<div>
    <a onclick="Dialog.clear(); Upload.xhr.abort();" class="button flat">
        {$c->__('button.close')}
    </a>
</div>
