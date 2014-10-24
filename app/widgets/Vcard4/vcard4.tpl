<div class="tabelem padded" title="{$c->__('Data')}" id="vcard4" >
    {if="!isset($me->jid)"}
        <script type="text/javascript">
            movim_add_onload(function() {
                {$getvcard}
            });
        </script>
    {/if}
    <div id="vcard_form">
        {$form}
    </div>
</div>
