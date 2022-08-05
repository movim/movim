<div class="tabelem padded_top_bottom" title="{$c->__('vcard.title')}" data-mobileicon="face" id="vcard4" >
    <div id="avatar" class="spin"></div>
    {if="!isset($me->jid)"}
        <script type="text/javascript">
            MovimWebsocket.attach(function() {
                Vcard4_ajaxGetVcard()
            });
        </script>
    {/if}
    <div id="vcard_form">
        {autoescape="off"}
            {$form}
        {/autoescape}
    </div>
</div>
