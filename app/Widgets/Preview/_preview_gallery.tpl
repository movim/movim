<ul class="list controls middle">
    <li>
        <span class="primary icon active" onclick="Preview_ajaxHttpHide()" title="{$c->__('button.close')}">
            <i class="material-symbols">arrow_back</i>
        </span>
    </li>
</ul>
{if="$url && !empty($url->images)"}
    <img src="{$url->images[$imagenumber]['url']|protectPicture}"
        title="{$url->images[$imagenumber]['url']}"
        class="transparent"/>

    {if="array_key_exists('size', $url->images[$imagenumber])"}
        <span class="pinfo">
            {if="$url->images[$imagenumber]['size'] != 0"}
                {$url->images[$imagenumber]['size']|humanSize}
            {/if}
        </span>
    {/if}

    {$previous = $imagenumber-1}
    <span class="prevnext prev {if="$imagenumber > 0"}enabled{/if}"
        onclick="Preview_ajaxHttpGallery('{$url->url}', {$previous})">
        <i class="material-symbols">chevron_left</i>
    </span>

    {$next = $imagenumber+1}
    <span class="prevnext next {if="$imagenumber+1 < count($url->images)"}enabled{/if}"
        onclick="Preview_ajaxHttpGallery('{$url->url}', {$next})">
        <i class="material-symbols">chevron_right</i>
    </span>

    <span class="counter">{$imagenumber+1} / {$url->images|count}</span>
    <div class="buttons">
        <a class="button flat color transparent" href="{$url->images[$imagenumber]['url']}" target="_blank" download title="{$c->__('button.save')}">
            <i class="material-symbols">get_app</i> {$c->__('button.save')}
        </a>
        <a class="button flat color transparent" href="#" onclick="Preview.copyToClipboard('{$url->images[$imagenumber]['url']}')" title="{$c->__('button.copy_link')}">
            <i class="material-symbols">content_copy</i> {$c->__('button.copy_link')}
        </a>
    </div>
{/if}
