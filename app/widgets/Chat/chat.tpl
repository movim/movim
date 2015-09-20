<div id="chat_widget" {if="$jid"}data-jid="{$jid}"{/if}>
    {if="$jid"}
        {$c->prepareChat($jid)}
    {else}
        {$c->prepareEmpty()}
    {/if}
</div>
