{if="$users->isNotEmpty()"}
    {autoescape="off"}
        {$c->prepareUsers($users)}
    {/autoescape}
{else}
    <div class="placeholder">
        <i class="material-icons">search</i>
        <h4>{$c->__('search.subtitle')}</h4>
    </div>
{/if}