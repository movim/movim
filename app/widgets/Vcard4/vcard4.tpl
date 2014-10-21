<div class="tabelem padded" title="{$c->__('Data')}" id="vcard4" >
    {if="!isset($me->jid)"}
        <script type="text/javascript">setTimeout('{$getvcard}', 500);</script>
    {/if}
    <div id="vcard_form">
        {$form}
    </div>
</div>
