<ul class="list thick">
    <li>
        <span class="primary icon bubble" style="background-image: url({$embed->images[0]['value']|htmlspecialchars});">
            <i class="zmdi zmdi-image"></i>
        </span>
        <p>{$c->__('post.gallery')}</p>
        <p class="list">
            <a href="{$embed->images[0]['value']|htmlspecialchars}" target="_blank">
                {$embed->images[0]['value']|htmlspecialchars}
            </a>
        </p>
    </li>
</ul>
