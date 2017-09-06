<ul class="list thick" id="presence_widget" dir="ltr">
    <li>
        {$url = $me->getPhoto('s')}
        {if="$url"}
            <span class="primary icon bubble color status">
                <img src="{$url}">
            </span>
        {else}
            <span class="primary icon bubble color status">
                <i class="zmdi zmdi-account"></i>
            </span>
        {/if}
        <p class="line bold"><br /></p>
        <p class="line"><br /></p>
    </li>
</ul>

