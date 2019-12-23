<div class="tabelem" title="{$c->__('vcard.title')}" id="vcard4" >
    {if="!isset($me->jid)"}
        <script type="text/javascript">
            MovimWebsocket.attach(function() {
                {$getvcard}
            });
        </script>
    {/if}
    <div id="vcard_form">
        {autoescape="off"}
            {$form}
        {/autoescape}
    </div>
</div>
