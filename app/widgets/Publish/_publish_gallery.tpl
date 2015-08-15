<ul class="thick">
    <li class="condensed">
        <span class="icon bubble" style="background-image: url({$embed->images[0]['value']|htmlspecialchars});">
            <i class="zmdi zmdi-image"></i>
        </span>
        <span>{$c->__('post.gallery')}</span>
        <p>
            <a href="{$embed->images[0]['value']|htmlspecialchars}" target="_blank">
                {$embed->images[0]['value']|htmlspecialchars}
            </a>
        </p>
    </li>
</ul>
