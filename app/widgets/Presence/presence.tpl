<ul class="list thick active" id="presence_widget" dir="ltr">
    <li>
        {$url = $me->getPhoto()}
        {if="$url"}
            <span class="primary icon bubble color status">
                <img src="{$url}">
            </span>
        {else}
            <span class="primary icon bubble color status">
                <i class="material-icons">person</i>
            </span>
        {/if}
        <p class="line bold"><br /></p>
        <p class="line"><br /></p>
    </li>
</ul>
