<span class="primary icon gray"><i class="material-symbols">{$icon}</i></span>
<div>
    <p><a href="{$file->url}" target="_blank" rel="noopener noreferrer">{$name}</a></p>
    {if="$size"}<p><span class="second">{$size}{if="$ext"} · <span class="ext">{$ext}</span>{/if}</span></p>{/if}
</div>