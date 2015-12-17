{if="isset($action)"}
<a href="{$action}">
{/if}
    <ul class="list">
        <li>
        {if="isset($picture)"}
            <span class="primary icon bubble"><img src="{$picture}"></span>
        {/if}
        <p>{$title}</p>
        {if="isset($body)"}
            <p>{$body}</p>
        {/if}
        </li>
    </ul>
{if="isset($action)"}
</a>
{/if}
