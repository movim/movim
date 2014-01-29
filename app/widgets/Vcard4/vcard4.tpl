<div class="tabelem padded" title="{$c->t('Data')}" id="vcard4" >
    {if="isset($me) && $me == null"}
        <script type="text/javascript">setTimeout('{$getvcard}', 500);</script>
    {/if}
    <div id="vcard_form">
        {$form}
    </div>
</div>
