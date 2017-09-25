<div class="tabelem padded_top_bottom" title="{$c->__('vcard.title')}" id="vcard4" >
    {if="!isset($me->jid)"}
        <script type="text/javascript">
            MovimWebsocket.attach(function() {
                {$getvcard}
            });
        </script>
    {/if}
    <div id="vcard_form">
        {$form}
    </div>
</div>
