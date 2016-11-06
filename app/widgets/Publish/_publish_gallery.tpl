<ul class="list thick">
    {if="$embed->images != null"}
    <li>
        <span class="primary icon bubble" style="background-image: url({$embed->images[0]['value']|htmlspecialchars});">
            <i class="zmdi zmdi-image"></i>
        </span>
        <p>{$c->__('publish.gallery')}</p>
        <p class="list">
            <a href="{$embed->images[0]['value']|htmlspecialchars}" target="_blank">
                {$embed->images[0]['value']|htmlspecialchars}
            </a>
        </p>
    </li>
    {/if}
</ul>
