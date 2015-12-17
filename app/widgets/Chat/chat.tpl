<div id="chat_widget" {if="$jid"}data-jid="{$jid}"{/if} style="background-color: #EEE;">
    {if="$jid"}
        {$c->prepareChat($jid)}
    {else}
        {$c->prepareEmpty()}
    {/if}
</div>
