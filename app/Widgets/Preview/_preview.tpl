<ul class="list controls middle">
    <li>
        <span class="primary icon active" onclick="history.back()" title="{$c->__('button.close')}">
            <i class="material-symbols">arrow_back</i>
        </span>
    </li>
</ul>
<img src="{$raw_url|protectPicture}" title="{$raw_url}" class="transparent"/>
{if="$url->type == 'image'"}
    <span class="pinfo">
        {if="$url->content_length != 0"}
            {$url->content_length|humanSize}
        {/if}
    </span>
{elseif="!empty($url->images)"}
    <span class="pinfo">
        {if="$url->images[0]['size'] != 0"}
            {$url->images[0]['size']|humanSize}
        {/if}
    </span>
{/if}
<span class="prevnext prev"></span>
<span class="prevnext next"></span>
<div class="buttons">
    <a class="button flat color transparent" href="{$raw_url}" target="_blank" download title="{$c->__('button.save')}">
        <i class="material-symbols">get_app</i> {$c->__('button.save')}
    </a>
    <a class="button flat color transparent" href="#" onclick="Preview.copyToClipboard('{$raw_url}')" title="{$c->__('button.copy_link')}">
        <i class="material-symbols">content_copy</i> {$c->__('button.copy_link')}
    </a>
    {if="$messageid"}
        <a class="button flat color transparent" href="#" onclick="Preview_ajaxHttpHide(); ChatActions_ajaxShowMessageDialog({$messageid});" title="{$c->__('button.more')}">
            <i class="material-symbols">more_vert</i>
        </a>
    {/if}
</div>
