<ul class="list navigation thick active on_desktop" id="presence_widget" dir="ltr">
    <li>
        <span class="primary icon bubble color status">
            {if="$c->me->contact"}
                <img src="{$c->me->contact->getPicture(\Movim\ImageSize::M)}">
            {/if}
        </span>
        <div>
            <p class="line bold"><br /></p>
            <p class="line"><br /></p>
        </div>
    </li>
</ul>
