<div class="tabelem" title="{$c->__('vcard.title')}" data-mobileicon="face" id="vcard4" >
    <br />
    <div id="avatar" class="spin"></div>
    {if="!isset($me->jid)"}
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function dcl() {
                document.removeEventListener('DOMContentLoaded', dcl, false);
                MovimWebsocket.attach(function() {
                    Vcard4_ajaxGetVcard()
                });
            }, false);
        </script>
    {/if}
    <div id="vcard_form">
        {autoescape="off"}
            {$form}
        {/autoescape}
    </div>
</div>
