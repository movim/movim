{if="$jid"}
    {$c->prepareContact($jid)}
{else}
    <div title="{$c->__('page.profile')}">
        {$c->prepareEmpty()}
    </div>
{/if}
