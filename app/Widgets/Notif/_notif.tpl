<ul class="list thick {if="isset($action) || isset($onclick)"}active{/if}"
    {if="isset($onclick)"}
        onclick="{$onclick}; Notif.snackbarClear();"
    {elseif="isset($action)"}
        onclick="MovimUtils.softRedirect('{$action}')"
    {/if}
>
    <li>
        {if="isset($picture)"}
            <span class="primary icon bubble"><img src="{$picture}"></span>
        {else}
            <span class="primary icon gray"><i class="material-symbols">notification</i></span>
        {/if}
        <div>
            <p class="normal line two">{$title}</p>
            {if="isset($body)"}
                <p class="line two">{$body}</p>
            {/if}
        </div>
    </li>
</ul>
