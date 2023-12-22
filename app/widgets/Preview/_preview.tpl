<ul class="list controls middle">
    <li>
        <span class="primary icon color transparent active" onclick="Preview_ajaxHttpHide()" title="{$c->__('button.close')}">
            <i class="material-symbols">arrow_back</i>
        </span>
    </li>
</ul>
<img src="{$url|protectPicture}" title="{$url}" class="transparent"/>
{if="!empty($embed->images) && array_key_exists('size', $embed->images[0])"}
    <span class="pinfo">
        {if="$embed->images[0]['size'] != 0"}
            {$embed->images[0]['size']|humanSize}
        {/if}
    </span>
{/if}
<span class="prevnext prev"></span>
<span class="prevnext next"></span>
<div class="buttons">
    <a class="button flat color transparent" href="{$url}" target="_blank" download title="{$c->__('button.save')}">
        <i class="material-symbols">get_app</i> {$c->__('button.save')}
    </a>
    <a class="button flat color transparent" href="#" onclick="Preview.copyToClipboard('{$url}')" title="{$c->__('button.copy_link')}">
        <i class="material-symbols">content_copy</i> {$c->__('button.copy_link')}
    </a>
    {if="$messageid"}
        <a class="button flat color transparent" href="#" onclick="Preview_ajaxHttpHide(); ChatActions_ajaxShowMessageDialog({$messageid});" title="{$c->__('button.more')}">
            <i class="material-symbols">more_vert</i>
        </a>
    {/if}
</div>
