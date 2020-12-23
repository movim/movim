<ul class="list controls">
    <li>
        <span class="primary icon color transparent active" onclick="Preview_ajaxHide()" title="{$c->__('button.close')}">
            <i class="material-icons">arrow_back</i>
        </span>
    </li>
</ul>
<img src="{$url|protectPicture}" title="{$url}" class="transparent"/>
<span class="prevnext prev"></span>
<span class="prevnext next"></span>
<div class="buttons">
    <a class="button flat color transparent" href="{$url}" target="_blank" download title="{$c->__('button.save')}">
        <i class="material-icons">get_app</i> {$c->__('button.save')}
    </a>
</div>
