<section>
    <h3>{$c->__('upload.title')}</h3>
    <ul class="list thick">
        <li>
            <span class="primary icon bubble color green">
                <i class="zmdi zmdi-upload"></i>
            </span>
            <p>{$c->__('upload.choose')}</p>
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
