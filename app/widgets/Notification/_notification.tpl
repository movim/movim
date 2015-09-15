{if="isset($action)"}
<a href="{$action}">
{/if}
    <ul class="{if="!isset($picture)"}simple{/if}">
        <li class="{if="isset($body)"}condensed{/if}">
        {if="isset($picture)"}
            <span class="icon bubble"><img src="{$picture}"></span>
        {/if}
        <span>{$title}</span>
        {if="isset($body)"}
            <p>{$body}</p>
        {/if}
        </li>
    </ul>
{if="isset($action)"}
</a>
{/if}
