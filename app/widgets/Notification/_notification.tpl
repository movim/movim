{if="isset($action)"}
<a href="{$action}">
{elseif="isset($onclick)"}
<a href="#" onclick="{$onclick}; Notification.snackbarClear();">
{/if}
    <ul class="list">
        <li>
        {if="isset($picture)"}
            <span class="primary icon bubble"><img src="{$picture}"></span>
        {/if}
        <div>
            <p>{$title}</p>
            {if="isset($body)"}
                <p>{$body}</p>
            {/if}
        </div>
        </li>
    </ul>
{if="isset($action) || isset($onclick)"}
</a>
{/if}
